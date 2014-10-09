<?php namespace TippingCanoe\LaravelOauth;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TippingCanoe\LaravelOauth\Model\Oauthable;
use TippingCanoe\LaravelOauth\Repository\AccessToken as AccessTokenRepository;
use TippingCanoe\LaravelOauth\Repository\Consumer as ConsumerRepository;
use Illuminate\Cache\CacheManager;
use TippingCanoe\LaravelOauth\Model\Consumer;
use TippingCanoe\LaravelOauth\Model\AccessToken;

use DateInterval;
use DateTime;
use OAuthProvider;
use Exception;


class Service {

	/** @var OAuthProvider  */
	protected $provider;

	/** @var boolean */
	protected $using2Leg;

	/** @var array */
	protected $oauthData;

	/** @var Request  */
	protected $request;

	/** @var CacheManager  */
	protected $cache;

	/** @var \TippingCanoe\LaravelOauth\Repository\AccessToken */
	protected $accessTokenRepository;

	/** @var \TippingCanoe\LaravelOauth\Repository\Consumer */
	protected $consumerRepository;

	/** @var DateInterval */
	protected $nonceTtl;

	/** @var Consumer */
	protected $consumer;

	/** @var  AccessToken */
	protected $accessToken;

	public function __construct(
		Request $request,
		CacheManager $cache,
		AccessTokenRepository $accessTokenRepository,
		ConsumerRepository $consumerRepository,
		DateInterval $nonceTtl,
		$debug = false
	) {
		$this->request = $request;
		$this->cache = $cache;
		$this->accessTokenRepository = $accessTokenRepository;
		$this->consumerRepository = $consumerRepository;
		$this->nonceTtl = $nonceTtl;
		$this->debug = $debug;
	}

	/**
	 * Refines Oauth parameters out from the current request.
	 *
	 * @param string $header
	 * @return array
	 */
	protected static function parseOauthParamsFromHeader($header) {

		$params = [];
		$matches = [];

		if(preg_match_all('/(oauth_[a-z_-]*)=(:?"([^"]*)"|([^,]*))/', $header, $matches)) {

			foreach($matches[1] as $key => $param) {

				$value = urldecode(empty($matches[3][$key]) ? $matches[4][$key] : $matches[3][$key]);

				if($param == 'realm')
					continue;

				if(!is_string($value))
					continue;

				$params[$param] = $value;

			}

		}

		return $params;

	}


	/**
	 * Resolves whether oauth functionality should be overridden.
	 *
	 * @return boolean
	 */
	public function canOverride() {
		return $this->debug;
	}

	/**
	 * Returns all oauth data supplied for the current request.
	 *
	 * @return null|array
	 */
	public function getOauthData() {

		if(!$this->oauthData) {

			// Only use post variables when the request content type is 'application/x-www-form-urlencoded' as per Oauth 1.0a spec.
			// See: http://oauth.net/core/1.0a/#rfc.section.9.1.1
			if($this->request->headers->get('CONTENT_TYPE') == 'application/x-www-form-urlencoded')
				$this->oauthData = $this->request->all();
			// All other requests will be verified using only the query string parameters.
			else
				$this->oauthData = $this->request->query->all();

			// Determine which header (if any) we're pulling Oauth data from.
			//
			// http://oauth.net/core/1.0#rfc.section.5.2
			// http://oauth.net/core/1.0/#auth_header
			if(
				($authHeader = $this->request->header('O-Authorization'))
				|| ($authHeader = $this->request->header('authorization'))
				|| ($authHeader = $this->request->header('Authorization'))
			) {
				$this->oauthData = array_merge($this->oauthData, static::parseOauthParamsFromHeader($authHeader));
			}

		}

		return $this->oauthData;

	}

	/**
	 * Returns an array with either an arbitrary string as it's only element or two elements representing type and id.
	 *
	 * @return array
	 */
	public function getOverride() {
		$override = explode(',', $this->request->get('oauth_override'));
		return $override[0] ? $override : null;
	}

	/**
	 * Returns the current access token.
	 *
	 * @return AccessToken
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}

	/**
	 * If the current request is authenticated, return the Oauthable associated with the token.
	 *
	 * @return null|Oauthable
	 */
	public function getOauthable() {
		$accessToken = $this->getAccessToken();
		return $accessToken ? $accessToken->oauthable : null;
	}

	/**
	 * Returns the current Consumer
	 *
	 * @return null|Consumer The current consumer or null when one hasn't been authenticated.
	 */
	public function getConsumer() {
		return $this->consumer;
	}

	/**
	 * Prepare an access token for an oauthable using the current consumer.
	 *
	 * @param \TippingCanoe\LaravelOauth\Model\Oauthable $oauthable
	 * @throws Exception
	 * @return AccessToken
	 */
	public function prepareAccessToken(Oauthable $oauthable) {

		if(!$this->consumer)
			throw new Exception(sprintf('Unable to create access token for oauthable \'%s#%s\' without a consumer context.', get_class($oauthable), $oauthable->getKey()));

		return $this->accessTokenRepository->findOrCreate($oauthable, $this->consumer) ?: null;

	}

	/**
	 * Conduct a request check.
	 *
	 * This method will throw exceptions based on the current state of the request.  This allows it to plug back into the
	 * http framework lifecycle.  The exceptions will range from native oauth, http and just plain exceptions.
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function checkRequest() {

		if($this->canOverride()	and $override = $this->getOverride()) {

			// Grab the override consumer.
			$this->consumer = $this->consumerRepository->findOrCreateByName('Override');

			// If we're trying to bypass in as a specific Oauthable.
			if(
				isset($override[0])
				&& isset($override[1])
				&& is_numeric($override[1])
				and !$this->accessToken = $this->accessTokenRepository->findOrCreateByTypeAndId($override[0], $override[1], $this->consumer->getKey())
			)
				throw new HttpException(400, sprintf('Unable to find an access token for oauthable \'%s#%s\'', $override[0], $override[1]));

			// If we're currently requiring an access token, but haven't overridden as an oauthable.
			if(!$this->using2Leg && !$this->accessToken)
				throw new HttpException(401, 'You must override as a specific oauthable to access this endpoint.');

		}
		// Conduct a regular check.
		else {
			// Now that we need one, create the provider.
			$this->provider = new OAuthProvider($this->getOauthData());
			$this->provider->consumerHandler(array($this, 'verifyConsumer'));
			$this->provider->timestampNonceHandler(array($this, 'verifyTimestampNonce'));
			$this->provider->tokenHandler(array($this, 'verifyToken'));
			$this->provider->isRequestTokenEndpoint(false);

			if($this->using2Leg)
				$this->provider->is2LeggedEndpoint(true);

			$this->provider->checkOAuthRequest();
		}

	}


	//
	//
	// Everything past this line is specific to the PHP oauth library and is best ignored!
	//
	//

	/**
	 * Tells the provider that the request does not require a User context. No access token verification will take place.
	 *
	 * @param bool $is
	 */
	public function is2LeggedEndpoint($is = true) {
		$this->using2Leg = $is;
	}

	/**
	 * Verifies that a(n) nonce has not been used.
	 *
	 * @param OAuthProvider $provider The provider that we're working with.
	 * @return int Status of the verification.
	 */
	public function verifyTimestampNonce(OAuthProvider $provider) {

		// https://github.com/laravel/framework/issues/2807
		if($nonce = $this->cache->section('nonce')->get($provider->nonce))
			return OAUTH_BAD_NONCE;

		// This may seem protracted, but this lets us use very arbitrary rules like "34 days" for expiry in the config.
		$now = new DateTime();
		$expiry = (new DateTime())->add($this->nonceTtl);
		$minutesToLive = round(($expiry->getTimestamp() - $now->getTimestamp()) / 60);

		// Add the nonce to our cache.
		$this->cache->section('nonce')->put($provider->nonce, $this->consumer->getKey(), $minutesToLive);

		return OAUTH_OK;

	}

	/**
	 * Verifies that the consumer is valid and enabled.
	 *
	 * @param OAuthProvider $provider The provider that we're working with.
	 * @return int Status of the verification.
	 */
	public function verifyConsumer(OAuthProvider $provider) {

		$consumer = $this->consumerRepository->getByKey($provider->consumer_key);

		// The consumer_key doesn't match any consumer we know of.
		if(!$consumer)
			return OAUTH_CONSUMER_KEY_UNKNOWN;
		// The consumer is disabled.
		elseif(!$consumer->enabled)
			return OAUTH_CONSUMER_KEY_REFUSED;

		// Everything checks out, save it up.
		$provider->consumer_secret = $consumer->secret;

		// Assign the consumer to the service.
		$this->consumer = $consumer;

		return OAUTH_OK;

	}

	/**
	 * Confirms a token and assigns the shared secret to the provider.
	 *
	 * @param  OAuthProvider $provider The provider we're working with.
	 * @throws \Exception
	 * @return int Status of the verification.
	 */
	public function verifyToken(OAuthProvider $provider) {

		if(!$this->consumer)
			throw new Exception('Token verification attempted without a consumer context.');

		$accessToken = $this->accessTokenRepository->getByKeyAndConsumerId($provider->token, $this->consumer->getKey());

		if($accessToken) {
			$provider->token_secret = $accessToken->secret;
			$this->accessToken = $accessToken;
			return OAUTH_OK;
		}

		return OAUTH_TOKEN_REJECTED;

	}

}