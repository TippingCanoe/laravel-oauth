<?php namespace TippingCanoe\LaravelOauth\Filter;

use TippingCanoe\LaravelOauth\Filter\Base;


class Consumer extends Base {

	public function filter() {

		$override = $this->oauthService->getOverride();
		$oauthData = $this->oauthService->getOauthData();

		// Routes secured with consumer-level authentication should still work for access-authenticated requests.
		if(
			(
				$this->oauthService->canOverride()
				&& isset($override[0])
				&& !isset($override[1])
			)
			|| (
				!empty($oauthData)
				&& empty($oauthData['oauth_token'])
			)
		) {
			// Assign the current user to the Laravel Auth system.
			$this->oauthService->is2LeggedEndPoint();
		}

		parent::filter();

	}

}