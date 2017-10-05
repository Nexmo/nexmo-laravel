<?php

namespace Nexmo\Laravel\Facade;

use Nexmo\Client;
use Illuminate\Support\Facades\Facade;

class Nexmo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
