<?php

namespace Nexmo\Laravel;

use Illuminate\Support\ServiceProvider;
use Nexmo\Client;

class NexmoServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $dist = __DIR__.'/../config/nexmo.php';
        $this->publishes([
            $dist => config_path('nexmo.php'),
        ]);

        $this->mergeConfigFrom($dist, 'nexmo');
    }

    public function register()
    {
        $this->app->singleton(Client::class, function($app){
            $config = $app['config']->get('nexmo');

            if(!$config){
                throw new \RuntimeException('missing nexmo configuration section');
            }

            if(!isset($config['api_key'])){
                throw new \RuntimeException('missing nexmo configuration: `api_key`');
            }

            if(isset($config['api_secret'])){
                $credentials = new Client\Credentials\Basic($config['api_key'], $config['api_secret']);
            } elseif(isset($config['signature_secret'])) {
                $credentials = new Client\Credentials\SharedSecret($config['api_key'], $config['signature_secret']);
            }

            if(!isset($credentials)){
                throw new \RuntimeException('missing nexmo configuration: `api_secret` or `signature_secret`');
            }

            $options = array_diff_key($config, ['api_key', 'api_secret', 'shared_secret']);

            return new Client($credentials, $options);
        });
    }

    public function provides()
    {
        return [Client::class];
    }
}
