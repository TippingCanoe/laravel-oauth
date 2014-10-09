<?php namespace TippingCanoe\LaravelOauth\Model;

use Illuminate\Database\Eloquent\Relations\MorphMany;


interface Oauthable {

	/**
	 * Returns the access token relation for the object.
	 *
	 * A default implementation is available via the OauthableImpl trait.
	 *
	 * @return MorphMany
	 */
	public function accessTokens();

}