<?php namespace TippingCanoe\LaravelOauth\Repository;

use TippingCanoe\LaravelOauth\Repository\Consumer as ConsumerRepository;
use TippingCanoe\LaravelOauth\Model\Consumer as ConsumerModel;

class DbConsumer implements ConsumerRepository {

	public function getById($id) {
		return ConsumerModel
			::remember(10)
			->find($id)
		;
	}

	public function getByName($name) {
		return ConsumerModel::named($name)->first();
	}

	public function getByKey($key) {
		return ConsumerModel
			::remember(10)
			->withKey($key)
			->first()
		;
	}

	public function findOrCreateByName($name) {

		if($consumer = $this->getByName($name))
			return $consumer;

		return ConsumerModel::create([
			'name' => $name,
			'enabled' => true
		]);

	}

}