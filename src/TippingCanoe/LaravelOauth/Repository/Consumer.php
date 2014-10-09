<?php namespace TippingCanoe\LaravelOauth\Repository;

interface Consumer {

	/**
	 * @param $id
	 * @return \TippingCanoe\LaravelOauth\Model\Consumer
	 */
	public function getById($id);

	/**
	 * @param $name
	 * @return \TippingCanoe\LaravelOauth\Model\Consumer
	 */
	public function getByName($name);

	/**
	 * @param $key
	 * @return \TippingCanoe\LaravelOauth\Model\Consumer
	 */
	public function getByKey($key);

	/**
	 * @param $name
	 * @return \TippingCanoe\LaravelOauth\Model\Consumer
	 */
	public function findOrCreateByName($name);

}