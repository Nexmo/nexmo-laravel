<?php

namespace Nexmo\Laravel\Facade;

use Illuminate\Support\Facades\Facade;
use Nexmo\Client;

class Nexmo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}