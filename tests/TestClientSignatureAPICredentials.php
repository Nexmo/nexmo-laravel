<?php

namespace Nexmo\Laravel\Tests;

use Nexmo\Client;

class TestClientSignatureAPICredentials extends AbstractTestCase
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
        $app['config']->set('nexmo.signature_secret', 'my_signature');
    }

    /**
     * Test that our Nexmo client is created with
     * the signature credentials
     *
     * @return void
     */
    public function testClientCreatedWithSignatureAPICredentials()
    {
        $client = app(Client::class);

        $credentialsObject = $this->getClassProperty(Client::class, 'credentials', $client);
        $credentialsArray = $this->getClassProperty(Client\Credentials\SignatureSecret::class, 'credentials', $credentialsObject);

        $this->assertInstanceOf(Client\Credentials\SignatureSecret::class, $credentialsObject);
        $this->assertEquals(['api_key' => 'my_api_key', 'signature_secret' => 'my_signature'], $credentialsArray);
    }
}
