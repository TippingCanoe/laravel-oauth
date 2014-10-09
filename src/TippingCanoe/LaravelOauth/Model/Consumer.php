<?php namespace TippingCanoe\LaravelOauth\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Consumer extends Model {

	protected $table = 'oauth_consumer';

	protected $fillable = [
		'name',
		'enabled'
	];

	public function __construct() {

		call_user_func_array([$this, 'parent::__construct'], func_get_args());

		if(!$this->getKey()) {
			$this->generateKey();
			$this->generateSecret();
		}

	}

	public function generateKey() {
		$this->key = uniqid();
	}

	public function generateSecret() {
		$this->secret = uniqid();
	}

	public function accessTokens() {
		return $this->hasMany('TippingCanoe\LaravelOauth\Model\AccessToken');
	}

	public function scopeWithKey(Builder $query, $key) {
		return $query->where('oauth_consumer.key', '=', $key);
	}

	public function scopeNamed(Builder $query, $name) {
		return $query->where('oauth_consumer.name', '=', $name);
	}

}