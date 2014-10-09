<?php namespace TippingCanoe\LaravelOauth\Model;


trait OauthableImpl {

	public function accessTokens() {
		return $this->morphMany('TippingCanoe\LaravelOauth\Model\AccessToken', 'oauthable');
	}

}