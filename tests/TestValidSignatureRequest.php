<?php

namespace Nexmo\Laravel\Tests;

use Nexmo\Client;
use Nexmo\Laravel\ValidSignatureRequest;
use Illuminate\Support\Facades\Validator;

class TestValidSignatureRequest extends AbstractTestCase
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
        $app['config']->set('nexmo.signature_secret', '71efab63122f1d179f51c46bac838fb5');
        $app['config']->set('nexmo.signature_method', 'sha256');

        \Route::post('/test-valid-signature', function (ValidSignatureRequest $request) {
            return response()->json("The data can be trusted");
        });
    }

    public function testFailsWithInvalidData()
    {
        $response = $this->call('POST', '/test-valid-signature', ['sig' => 'invalid']);
        $response->assertStatus(422);
    }

    public function testPassesWithValidData()
    {
        $response = $this->call('POST', '/test-valid-signature', array (
            'sig' => '9FEC5EF6D0F2B3D2BB7558B6E4042569823CAB9EA0DD30503472B7B304601975',
            'api_key' => 'fake_api_key',
            'to' => '14155550100',
            'from' => 'AcmeInc',
            'text' => 'Test From Nexmo',
            'type' => 'text',
            'timestamp' => '1540924779',
        ));
        $response->assertStatus(200);
    }
}

