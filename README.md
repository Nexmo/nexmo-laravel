Nexmo Package for Laravel
=========================
This is a simple Laravel Service Provider providing access to the  [Nexmo PHP Client Library][client-library].

Installation
------------

To install the PHP client library using Composer:

    composer require nexmo/laravel

*Importaint note*: While the client library is in `beta`, to avoid changing the `minimum-stability` in your 
`composer.json` require `nexmo/client` as well, using `@beta` as the version.
    
    composer require nexmo/client @beta
    
Then add `Nexmo\Laravel\NexmoServiceProvider` to the `providers` array in your `config/app.php`:

    Nexmo\Laravel\NexmoServiceProvider::class
    
If you want to use the facade interface, you can `use` the facade class when needed:
 
    use Nexmo\Laravel\Facade\Nexmo;
    
Or add an alias in your `config/app.php`:

    'Nexmo' => \Nexmo\Laravel\Facade\Nexmo::class

Configuration
-------------
 
You can use `artisan vendor:publish` to copy the distribution configuration file to your app's config directory:

    php artisan vendor:publish
    
Then set either the `api_key` and `api_secret`, or the `api_key` and `signature_secret`.

Usage
-----
   
To use the Nexmo Client Library you can use the facade, or request the instance from the service container:

    Nexmo::message()->send([
        'to' => '14845551244',
        'from' => '16105552344',
        'text' => 'Using the facad to send a mesage.'
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