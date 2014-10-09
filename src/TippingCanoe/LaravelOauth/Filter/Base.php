<?php namespace TippingCanoe\LaravelOauth\Filter;

use TippingCanoe\LaravelOauth\Service;
use Illuminate\Auth\AuthManager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use OAuthException;


abstract class Base {

	/** @var \TippingCanoe\LaravelOauth\Service */
	protected $oauthService;

	/** @var \Illuminate\Auth\AuthManager */
	protected $auth;

	public function __construct(
		Service $oauthService,
		AuthManager $auth
	) {
		$this->oauthService = $oauthService;
		$this->auth = $auth;
	}

	/**
	 * Base filtering check behaviour.
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 * @internal param $ [type] $route   [description]
	 * @internal param $ [type] $request [description]
	 * @return void [type]          [description]
	 */
	public function filter() {

		try {

			$this->oauthService->checkRequest();

			if($oauthable = $this->oauthService->getOauthable())
				$this->auth->login($oauthable);

		}
		catch(OAuthException $ex) {

			switch($ex->getCode()) {

				// Bad nonce.
				case 4:
					$code = 400;
					$message = "oauth.nonce_invalid";
				break;

				// Consumer key unknown.
				case 16:
					$code = 400;
					$message = "oauth.consumer_doesnt_exist";
				break;

				// Key refused.
				case 32:
					$code = 400;
					$message = "oauth.consumer_invalid";
				break;

				// Invalid signature.
				case 64:
					$code = 400;
					$message = "oauth.signature_invalid";
				break;

				case 1024:
					$code = 400;
					$message = "oauth.invalid_token";
				break;

				// Parameter absent.
				case 4096:
					$code = 400;
					$message = "oauth.signature_missing_parameter";
				break;

				case 8192:
					$code = 400;
					$message = "oauth.signature_method_rejected";
				break;

				case 128:		// Token used.
				default:
					$code = 500;
					$message = $ex->getMessage() . ' - ' . $ex->getCode();
				break;

			}

			throw new HttpException($code, $message, $ex);

		}

	}

}