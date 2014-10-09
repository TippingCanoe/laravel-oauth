<?php namespace TippingCanoe\LaravelOauth;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as Base;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use TippingCanoe\LaravelOauth\Service;


class ServiceProvider extends Base {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot() {
		$this->package('tippingcanoe/laravel-oauth');
		$this->app->singleton('Illuminate\Auth\AuthManager', 'auth');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {

		//
		// IoC
		//
		$this->app->singleton('TippingCanoe\LaravelOauth\Service', function (Application $app) {
			return new Service(
				$app['request'],
				$app['cache'],
				$app['TippingCanoe\LaravelOauth\Repository\AccessToken'],
				$app['TippingCanoe\LaravelOauth\Repository\Consumer'],
				$app['config']->get('laravel-oauth::nonce_ttl'),
				$app['config']->get('app.debug')
			);
		});

		// Bind our repositories;
		$this->app->bind('TippingCanoe\LaravelOauth\Repository\AccessToken', 'TippingCanoe\LaravelOauth\Repository\DbAccessToken');
		$this->app->bind('TippingCanoe\LaravelOauth\Repository\Consumer', 'TippingCanoe\LaravelOauth\Repository\DbConsumer');

		//
		// Commands
		//
		$this->commands(
			'TippingCanoe\LaravelOauth\Command\AddConsumerCommand'
		);

		//
		// Filters
		//
		$this->app['router']->filter('oauth-consumer', 'TippingCanoe\LaravelOauth\Filter\Consumer');
		$this->app['router']->filter('oauth-access', 'TippingCanoe\LaravelOauth\Filter\Access');

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return array();
	}

}