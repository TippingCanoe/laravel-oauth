<?php namespace TippingCanoe\LaravelOauth\Repository;

use TippingCanoe\LaravelOauth\Repository\AccessToken as AccessTokenRepository;
use TippingCanoe\LaravelOauth\Model\AccessToken as AccessTokenModel;
use TippingCanoe\LaravelOauth\Model\Oauthable;
use TippingCanoe\LaravelOauth\Model\Consumer as ConsumerModel;


class DbAccessToken implements AccessTokenRepository {

	/**
	 * @param Oauthable $oauthable
	 * @param \TippingCanoe\LaravelOauth\Model\Consumer $consumer
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function findOrCreate(Oauthable $oauthable, ConsumerModel $consumer) {

		$accessToken = $oauthable
			->accessTokens()
			->forConsumer($consumer->getKey())
			->first()
		;

		// Only create if one doesn't exist already for the consumer.
		if(!$accessToken) {

			// Remember, tokens will be automatically generated during instantiation.
			$accessToken = AccessTokenModel::create([
				'consumer_id' => $consumer->getKey(),
				'oauthable_type' => get_class($oauthable),
				'oauthable_id' => $oauthable->getKey(),
			]);

		}

		return $accessToken;

	}

	/**
	 * @param string $type
	 * @param int $id
	 * @param int $consumerId Optional consumer_id to filter by.
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function getByTypeAndId($type, $id, $consumerId = null) {

		$query = AccessTokenModel
			::forType($type)
			->forId($id)
		;

		if($consumerId)
			$query->forConsumer($consumerId);

		return $query->first();

	}

	/**
	 * @param string $type
	 * @param int $id
	 * @param int $consumerId Optional consumer_id to filter by.
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function findOrCreateByTypeAndId($type, $id, $consumerId) {

		$accessToken = $this->getByTypeAndId($type, $id, $consumerId);

		if(!$accessToken) {
			$accessToken = AccessTokenModel::create([
				'oauthable_type' => $type,
				'oauthable_id' => $id,
				'consumer_id' => $consumerId
			]);
		}

		return $accessToken;

	}

	/**
	 * @param string $key
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function getByKey($key) {
		return AccessTokenModel
			::withKey($key)
			->first()
		;
	}

	/**
	 * @param string $key
	 * @param int $consumerId
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function getByKeyAndConsumerId($key, $consumerId) {
		return AccessTokenModel
			::withKey($key)
			->forConsumer($consumerId)
			->first()
		;
	}

}