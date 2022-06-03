<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Deegitalbe\LaravelTrustupIoAuthentification\LaravelTrustupIoAuthentification
 */
class LaravelTrustupIoAuthentification extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-trustup-io-authentification';
    }
}
