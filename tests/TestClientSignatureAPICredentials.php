<?php

namespace Nexmo\Laravel\Tests;

use Nexmo\Client;
use Illuminate\Support\Facades\Validator;

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
        $app['config']->set('nexmo.signature_method', 'md5hash');
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
        $this->assertEquals(['api_key' => 'my_api_key', 'signature_secret' => 'my_signature', 'signature_method' => 'md5hash'], $credentialsArray);
    }

    /*
     * Test that the `nexmo_signature` validation rule is
     * accessible when using Validator::make().
     */
    public function testValidationExtensionIsRegistered()
    {
        $actual = Validator::make(['sig' => 'invalid_sig'], [
            'sig' => 'nexmo_signature'
        ])->passes();

        $this->assertFalse($actual);
    }

    /*
     * Now that we know it's registered, we need to make sure that
     * it runs implicitly when data is not provided
     */
    public function testValidationExtensionIsRegisteredImplicitly()
    {
        $actual = Validator::make([], [
            'sig' => 'nexmo_signature'
        ])->passes();

        $this->assertFalse($actual);
    }
}
