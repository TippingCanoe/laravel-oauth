# Laravel Oauth

A suite of tools to authenticate requests with consumer and access token credentials.

## Setup

### Migrations

This library depends on the existence of some tables in your database.  You can update your database schema using the package's migration.

	`./artisan migrate --package=tippingcanoe/laravel-oauth`

### Configuration

Laravel Oauth maintains a default configuration file which can be duplicated if necessary.

	./artisan config:publish tippingcanoe/laravel-oauth

### Models

Generally speaking, Oauth allows you to authenticate consumers as well as any other type of model representing an access context - in most cases, this is typically a `User` class.

To enable oauth functionality for a model, it only needs to implement the `Oauthable` interface.

As a convenience, the most common implementation of the oauthable interface is also available as a trait which ensures your models will always stay in sync with the library.

```
use TippingCanoe\LaravelOauth\Model\Oauthable;
use TippingCanoe\LaravelOauth\Model\OauthableImpl;


class User implements Oauthable {

	use OauthableImpl;

}
```

This is all you have to do to enable oauth for a model in your application.  Laravel Oauth will transparently support different types of models for access tokens.

### Service Provider

Be sure to add the Laravel Oauth service provider to your `config/app.php` file in the providers array.

```
		'TippingCanoe\LaravelOauth\ServiceProvider'
```

## Usage

Laravel Oauth comes with two filters which you can apply to your routes.  `consumer` and `access`.  It's worth noting that the consumer access filter will also allow fully authenticated requests through.


### Access Tokens aka "Logging In"

Access tokens don't exist for an oauthable until they are created.  When created, an access token will belong to the consumer context active during it's creation.

Assuming you're using dependency injection to receive an instance of `TippingCanoe\LaravelOauth\Service`, creating an access token is simple:

```
$oauthable = /* Your instance of oauthable here (again, typically User). */;
$accessToken = $this->oauthService->prepareAccessToken($oauthable);

```

After being created, an access token should be sent to the client.

### Overriding & Testing

During development, it might be easier to be able to bypass the oauth check mechanism entirely or to become a specific oauthable without looking up their credentials.

Any request that takes place in an environment with `app.debug` set to `true` will have the option of specifying `oauth_override` as a get parameter.  This variable accepts two formats:

```
http://localhost:8000?oauth_override=yes
```

or

```
http://localhost:8000?oauth_override=My\Project\Model\User,35
```

In the first example, the current request will use a dummy consumer for consumer-only access.  In the second example, an access token will be generated under the dummy consumer for the supplied oauthable type/id pair.


## Contact
* [Alexander Trauzzi](mailto:a.trauzzi@tippingcanoe.com)