<?php namespace TippingCanoe\LaravelOauth\Repository;

use TippingCanoe\LaravelOauth\Model\Consumer as ConsumerModel;
use TippingCanoe\LaravelOauth\Model\Oauthable;


interface AccessToken {

	/**
	 * @param Oauthable $oauthable
	 * @param ConsumerModel $consumer
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function findOrCreate(Oauthable $oauthable, ConsumerModel $consumer);

	/**
	 * @param string $type
	 * @param int $id
	 * @param int $consumerId Optional consumer_id to filter by.
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function getByTypeAndId($type, $id, $consumerId = null);

	/**
	 * @param string $type
	 * @param int $id
	 * @param int $consumerId consumer_id to use when searching or creating.
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function findOrCreateByTypeAndId($type, $id, $consumerId);


	/**
	 * @param string $key
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function getByKey($key);

	/**
	 * @param string $key
	 * @param int $consumerId
	 * @return null|\TippingCanoe\LaravelOauth\Model\AccessToken
	 */
	public function getByKeyAndConsumerId($key, $consumerId);


}