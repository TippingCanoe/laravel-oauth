<?php namespace TippingCanoe\LaravelOauth\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class AccessToken extends Model {

	protected $table = 'oauth_access_token';

	protected $fillable = [
		'consumer_id',
		'oauthable_type',
		'oauthable_id'
	];

	public function __construct() {

		call_user_func_array([$this, 'parent::__construct'], func_get_args());

		if(!$this->getKey()) {
			$this->generateKey();
			$this->generateSecret();
		}

	}

	public function oauthable() {
		return $this->morphTo();
	}

	public function generateKey() {
		$this->key = uniqid();
	}

	public function generateSecret() {
		$this->secret = uniqid();
	}

	public function consumer() {
		return $this->belongsTo('TippingCanoe\LaravelOauth\Model\Consumer');
	}

	public function scopeForConsumer(Builder $query, $consumerId) {
		return $query->where('oauth_access_token.consumer_id', '=', $consumerId);
	}

	public function scopeForType(Builder $query, $type) {
		return $query->where('oauth_access_token.oauthable_type', '=', $type);
	}

	public function scopeForId(Builder $query, $id) {
		return $query->where('oauth_access_token.oauthable_id', '=', $id);
	}

	public function scopeWithKey(Builder $query, $key) {
		return $query->where('oauth_access_token.key', '=', $key);
	}

}