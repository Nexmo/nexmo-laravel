<?php

namespace Nexmo\Laravel;

use Nexmo\Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;

class NexmoServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Config file path.
        $dist = __DIR__.'/../config/nexmo.php';

        // Publishes config File.
        $this->publishes([
            $dist => config_path('nexmo.php'),
        ]);

        // Merge config.
        $this->mergeConfigFrom($dist, 'nexmo');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind Nexmo Client in Service Container.
        $this->app->singleton(Client::class, function ($app) {
            return $this->createNexmoClient($app['config']);
        });
    }

    /**
     * Create a new Nexmo Client.
     *
     * @param Config $config
     *
     * @return Client;
     */
    protected function createNexmoClient(Config $config)
    {
        // Check for Nexmo config file.
        if (! $config->has('nexmo')) {
            throw new \RuntimeException('missing nexmo configuration section');
        }

        // Check for API_KEY.
        if (! $config->has('nexmo.api_key')) {
            throw new \RuntimeException('missing nexmo configuration: `api_key`');
        }

        // Check whether config is setup
        // for using API_SECRET
        // otherwise use
        // SIGNATURE
        if ($config->has('nexmo.api_secret')) {
            // Create Basic Credentials.
            $credentials = new Client\Credentials\Basic(
                $config->get('nexmo.api_key'), $config->get('nexmo.api_secret')
            );
        } elseif ($config->has('nexmo.signature_secret')) {
            // Create SharedSecret Credentials.
            $credentials = new Client\Credentials\SharedSecret(
                $config->get('nexmo.api_key'), $config->get('nexmo.signature_secret')
            );
        }

        if (! isset($credentials)) {
            throw new \RuntimeException('missing nexmo configuration: `api_secret` or `signature_secret`');
        }

        // Get Client Options.
        $options = array_diff_key($config->get('nexmo'), ['api_key', 'api_secret', 'shared_secret']);

        return new Client($credentials, $options);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Client::class];
    }
}
