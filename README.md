> THIS PACKAGE IS ABANDONDED
> Please use https://github.com/Vonage/vonage-laravel.
> Pull Requests and Issues opened up on this repo will not be addressed.

<h2 align="center">
    Nexmo Package for Laravel
</h2>

<p align="center">
    <a href="https://packagist.org/packages/nexmo/laravel"><img src="https://poser.pugx.org/nexmo/laravel/v/stable?format=flat-square" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/nexmo/laravel"><img src="https://poser.pugx.org/nexmo/laravel/v/unstable?format=flat-square" alt="Latest Unstable Version"></a>    
    <a href="https://packagist.org/packages/nexmo/laravel"><img src="https://poser.pugx.org/nexmo/laravel/license?format=flat-square" alt="License"></a>
    <a href="https://packagist.org/packages/nexmo/laravel"><img src="https://poser.pugx.org/nexmo/laravel/downloads" alt="Total Downloads"></a>
</p>

<img src="https://developer.nexmo.com/assets/images/Vonage_Nexmo.svg" height="48px" alt="Nexmo is now known as Vonage" />

## Introduction

This is a simple Laravel Service Provider providing access to the  [Nexmo PHP Client Library][client-library].

Installation
------------

To install the PHP client library using Composer:

```bash
composer require nexmo/laravel
```

Alternatively, add these two lines to your composer require section:

```json
{
    "require": {
        "nexmo/laravel": "^2.0"
    }
}
```

### Laravel 5.5+

If you're using Laravel 5.5 or above, the package will automatically register the `Nexmo` provider and facade.

### Laravel 5.4 and below

Add `Nexmo\Laravel\NexmoServiceProvider` to the `providers` array in your `config/app.php`:

```php
'providers' => [
    // Other service providers...

    Nexmo\Laravel\NexmoServiceProvider::class,
],
```

If you want to use the facade interface, you can `use` the facade class when needed:

```php
use Nexmo\Laravel\Facade\Nexmo;
```

Or add an alias in your `config/app.php`:

```php
'aliases' => [
    ...
    'Nexmo' => Nexmo\Laravel\Facade\Nexmo::class,
],
```

### Using Nexmo-Laravel with Lumen

Nexmo-Laravel works with Lumen too! You'll need to do a little work by hand
to get it up and running. First, install the package using composer:


```bash
composer require nexmo/laravel
```

Next, we have to tell Lumen that our library exists. Update `bootstrap/app.php`
and register the `NexmoServiceProvider`:

```php
$app->register(Nexmo\Laravel\NexmoServiceProvider::class);
```

Finally, we need to configure the library. Unfortunately Lumen doesn't support
auto-publishing files so you'll have to create the config file yourself by creating
a config directory and copying the config file out of the package in to your project:

```bash
mkdir config
cp vendor/nexmo/laravel/config/nexmo.php config/nexmo.php
```

At this point, set `NEXMO_KEY` and `NEXMO_SECRET` in your `.env` file and it should
be working for you. You can test this with the following route:

```php
$router->get('/', function () use ($router) {
    app(Nexmo\Client::class);
});
```

### Dealing with Guzzle Client issues

By default, this package uses `nexmo/client`, which includes a Guzzle adapter for accessing
the API. Some other libraries supply their own Guzzle adapter, leading to composer not being
able to resolve a list of dependencies. You may get an error when adding `nexmo/laravel` to
your application because of this.

The Nexmo client allows you to override the HTTP adapter that is being used. This takes a
bit more configuration, but this package allows you to use `nexmo/client-core` to supply your
own HTTP adapter.

To do this:

1. `composer require nexmo/client-core` to install the Core SDK
2. Install your own `httplug`-compatible adapter. For example, to use Symfony's HTTP Client:
    1. `composer require symfony/http-client php-http/message-factory php-http/httplug nyholm/psr7`
3. `composer require nexmo/laravel` to install this package
4. In your `.env` file, add the following configuration:

    `NEXMO_HTTP_CLIENT="Symfony\\Component\\HttpClient\\HttplugClient"`

You can now pull the `Nexmo\Client` object from the Laravel Service Container, or use the Facade
provided by this package.

Configuration
-------------

You can use `artisan vendor:publish` to copy the distribution configuration file to your app's config directory:

```bash
php artisan vendor:publish
```

Then update `config/nexmo.php` with your credentials. Alternatively, you can update your `.env` file with the following:

```dotenv
NEXMO_KEY=my_api_key
NEXMO_SECRET=my_secret
```

Optionally, you could also set an `application_id` and `private_key` if required:

```dotenv
NEXMO_APPLICATION_ID=my_application_id
NEXMO_PRIVATE_KEY=./private.key
```

Private keys can either be a path to a file, like above, or the string of the key itself:

```dotenv
NEXMO_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n[...]\n-----END PRIVATE KEY-----\n"
```

```dotenv
NEXMO_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----
[...]
-----END PRIVATE KEY-----
"
```

Usage
-----
   
To use the Nexmo Client Library you can use the facade, or request the instance from the service container:

```php
Nexmo::message()->send([
    'to'   => '14845551244',
    'from' => '16105552344',
    'text' => 'Using the facade to send a message.'
]);
```

Or

```php
$nexmo = app('Nexmo\Client');

$nexmo->message()->send([
    'to'   => '14845551244',
    'from' => '16105552344',
    'text' => 'Using the instance to send a message.'
]);
```

If you're using private key authentication, try making a voice call:

```php
Nexmo::calls()->create([
    'to' => [[
        'type' => 'phone',
        'number' => '14155550100'
    ]],
    'from' => [
        'type' => 'phone',
        'number' => '14155550101'
    ],
    'answer_url' => ['https://example.com/webhook/answer'],
    'event_url' => ['https://example.com/webhook/event']
]);
```

For more information on using the Nexmo client library, see the [official client library repository][client-library].

[client-library]: https://github.com/Nexmo/nexmo-php
