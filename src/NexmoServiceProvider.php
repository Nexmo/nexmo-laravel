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

        // If we're installing in to a Lumen project, config_path
        // won't exist so we can't auto-publish the config
        if (function_exists('config_path')) {
            // Publishes config File.
            $this->publishes([
                $dist => config_path('nexmo.php'),
            ]);
        }

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
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Client::class];
    }

    /**
     * Create a new Nexmo Client.
     *
     * @param Config $config
     *
     * @return Client
     *
     * @throws \RuntimeException
     */
    protected function createNexmoClient(Config $config)
    {
        // Check for Nexmo config file.
        if (! $this->hasNexmoConfigSection()) {
            $this->raiseRunTimeException('Missing nexmo configuration section.');
        }

        // Check for API_KEY.
        if ($this->nexmoConfigHasNo('api_key')) {
            $this->raiseRunTimeException('Missing nexmo configuration: "api_key".');
        }

        // Neither type of Credentials could be resolved from config.
        if ($this->nexmoConfigHasNo('api_secret') && $this->nexmoConfigHasNo('signature_secret')) {
            $this->raiseRunTimeException('Missing nexmo configuration: "api_secret" or "signature_secret".');
        }

        // Get Client Options.
        $options = array_diff_key($config->get('nexmo'), ['api_key', 'api_secret', 'shared_secret']);

        // Check whether config is setup for using API_SECRET
        // otherwise use SIGNATURE.
        if ($this->nexmoConfigHas('api_secret')) {
            return new Client(
                $this->createBasicCredentials($config->get('nexmo.api_key'), $config->get('nexmo.api_secret')),
                $options
            );
        }

        return new Client(
            $this->createSignatureCredentials($config->get('nexmo.api_key'), $config->get('nexmo.signature_secret')),
            $options
        );
    }

    /**
     * Checks if has global Nexmo configuration section.
     *
     * @return bool
     */
    protected function hasNexmoConfigSection()
    {
        return $this->app->make(Config::class)
                         ->has('nexmo');
    }

    /**
     * Checks if Nexmo config does not
     * have a value for the given key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function nexmoConfigHasNo($key)
    {
        return ! $this->nexmoConfigHas($key);
    }

    /**
     * Checks if Nexmo config has value for the
     * given key.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function nexmoConfigHas($key)
    {
        /** @var Config $config */
        $config = $this->app->make(Config::class);

        // Check for Nexmo config file.
        if (! $config->has('nexmo')) {
            return false;
        }

        return
            $config->has('nexmo.'.$key) &&
            ! is_null($config->get('nexmo.'.$key)) &&
            ! empty($config->get('nexmo.'.$key));
    }

    /**
     * Create a Basic credentials for client.
     *
     * @param string $key
     * @param string $secret
     *
     * @return Client\Credentials\Basic
     */
    protected function createBasicCredentials($key, $secret)
    {
        return new Client\Credentials\Basic($key, $secret);
    }

    /**
     * Create SignatureSecret credentials for client.
     *
     * @param string $key
     * @param string $signatureSecret
     *
     * @return Client\Credentials\SignatureSecret
     */
    protected function createSignatureCredentials($key, $signatureSecret)
    {
        return new Client\Credentials\SignatureSecret($key, $signatureSecret);
    }

    /**
     * Raises Runtime exception.
     *
     * @param string $message
     *
     * @throws \RuntimeException
     */
    protected function raiseRunTimeException($message)
    {
        throw new \RuntimeException($message);
    }
}
