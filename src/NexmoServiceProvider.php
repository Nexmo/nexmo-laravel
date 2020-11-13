<?php

namespace Nexmo\Laravel;

use Vonage\Client;
use Nexmo\Client as NexmoClient;
use Illuminate\Support\Str;
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
        $this->app->singleton(NexmoClient::class, function ($app) {
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
        return [
            Client::class,
            NexmoClient::class
        ];
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

        // Get Client Options.
        $options = array_diff_key($config->get('nexmo'), ['private_key', 'application_id', 'api_key', 'api_secret', 'shared_secret', 'app']);

        // Do we have a private key?
        $privateKeyCredentials = null;
        if ($this->nexmoConfigHas('private_key')) {
            if ($this->nexmoConfigHasNo('application_id')) {
                $this->raiseRunTimeException('You must provide nexmo.application_id when using a private key');
            }

            $privateKeyCredentials = $this->createPrivateKeyCredentials($config->get('nexmo.private_key'), $config->get('nexmo.application_id'));
        }

        $basicCredentials = null;
        if ($this->nexmoConfigHas('api_secret')) {
            $basicCredentials = $this->createBasicCredentials($config->get('nexmo.api_key'), $config->get('nexmo.api_secret'));
        }

        $signatureCredentials = null;
        if ($this->nexmoConfigHas('signature_secret')) {
            $signatureCredentials = $this->createSignatureCredentials($config->get('nexmo.api_key'), $config->get('nexmo.signature_secret'));
        }

        // We can have basic only, signature only, private key only or
        // we can have private key + basic/signature, so let's work out
        // what's been provided
        if ($basicCredentials && $signatureCredentials) {
            $this->raiseRunTimeException('Provide either nexmo.api_secret or nexmo.signature_secret');
        }

        if ($privateKeyCredentials && $basicCredentials) {
            $credentials = new Client\Credentials\Container(
                $privateKeyCredentials,
                $basicCredentials
            );
        } else if ($privateKeyCredentials && $signatureCredentials) {
            $credentials = new Client\Credentials\Container(
                $privateKeyCredentials,
                $signatureCredentials
            );
        } else if ($privateKeyCredentials) {
            $credentials = $privateKeyCredentials;
        } else if ($signatureCredentials) {
            $credentials = $signatureCredentials;
        } else if ($basicCredentials) {
            $credentials = $basicCredentials;
        } else {
            $possibleNexmoKeys = [
                'api_key + api_secret',
                'api_key + signature_secret',
                'private_key + application_id',
                'api_key + api_secret + private_key + application_id',
                'api_key + signature_secret + private_key + application_id',
            ];
            $this->raiseRunTimeException(
                'Please provide Nexmo API credentials. Possible combinations: '
                . join(", ", $possibleNexmoKeys)
            );
        }

        $httpClient = null;
        if ($this->nexmoConfigHas('http_client')) {
            $httpClient = $this->app->make($config->get(('nexmo.http_client')));
        }

        return new Client($credentials, $options, $httpClient);
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
     * Create Keypair credentials for client.
     *
     * @param string $key
     * @param string $applicationId
     *
     * @return Client\Credentials\Keypair
     */
    protected function createPrivateKeyCredentials($key, $applicationId)
    {
        return new Client\Credentials\Keypair($this->loadPrivateKey($key), $applicationId);
    }

    /**
     * Load private key contents from root directory
     */
    protected function loadPrivateKey($key)
    {
        if (app()->runningUnitTests()) {
            return '===FAKE-KEY===';
        }

        if (Str::startsWith($key, '-----BEGIN PRIVATE KEY-----')) {
            return $key;
        }

        // If it's a relative path, start searching in the
        // project root
        if ($key[0] !== '/') {
            $key = base_path().'/'.$key;
        }

        return file_get_contents($key);
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
