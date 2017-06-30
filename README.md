Nexmo Package for Laravel
=========================
This is a simple Laravel Service Provider providing access to the  [Nexmo PHP Client Library][client-library].

Installation
------------

To install the PHP client library using Composer:
    
    composer require nexmo/laravel 

*Important note*: While the client library is in `beta`, requiring `nexmo/client` specifically avoids changing the 
`minimum-stability` in your `composer.json`.

Alternatively, add these two lines to your composer require section:

    "nexmo/laravel": "0.4.0"

### Laravel 5.5+

If you're using Laravel 5.5 or above, the package will automatically register the `Nexmo` provider and facade.

### Laravel 5.4 and below

Add `Nexmo\Laravel\NexmoServiceProvider` to the `providers` array in your `config/app.php`:

    Nexmo\Laravel\NexmoServiceProvider::class
    
If you want to use the facade interface, you can `use` the facade class when needed:
 
    use Nexmo\Laravel\Facade\Nexmo;
    
Or add an alias in your `config/app.php`:

    'Nexmo' => \Nexmo\Laravel\Facade\Nexmo::class

Configuration
-------------
 
You can use `artisan vendor:publish` to copy the distribution configuration file to your app's config directory:

    php artisan vendor:publish
    
Then update `config/nexmo.php` with your credentials. You can also update your `.env` file with the following:
```
NEXMO_KEY = my_api_key
NEXMO_SECRET = my_secret
```

Usage
-----
   
To use the Nexmo Client Library you can use the facade, or request the instance from the service container:

    Nexmo::message()->send([
        'to' => '14845551244',
        'from' => '16105552344',
        'text' => 'Using the facade to send a message.'
    ]);

    //or
    
    $nexmo = app('Nexmo\Client');
    $nexmo->message()->send([
        'to' => '14845551244',
        'from' => '16105552344',
        'text' => 'Using the instance to send a message.'
    ]);
 
    
For more information on using the Nexmo client library, see the [official client library repository][client-library]. 

[client-library]: https://github.com/Nexmo/nexmo-php
