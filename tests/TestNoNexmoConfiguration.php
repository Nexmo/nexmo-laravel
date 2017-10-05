<?php

namespace Nexmo\Laravel\Tests;

use Nexmo\Client;

class TestNoNexmoConfiguration extends AbstractTestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('nexmo.api_key', 'my_api_key');
    }

    /**
     * Test that when we do not supply Nexmo configuration
     * a Runtime exception is generated.
     *
     * @return void
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Missing nexmo configuration: "api_secret" or "signature_secret".
     */
    public function testWhenNoConfigurationIsGivenExceptionIsRaised()
    {
        app(Client::class);
    }
}
