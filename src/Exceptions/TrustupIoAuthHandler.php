<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification\Exceptions;

use Deegitalbe\LaravelTrustupIoAuthentification\Contracts\Exceptions\SkipAuthContextContract;
use Henrotaym\LaravelFlareExceptionHandler\FlareExceptionHandler;
use Illuminate\Support\Facades\Log;

class TrustupIoAuthHandler extends FlareExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (SkipAuthContextContract $e) {
            $this->reportContextToFlare($e);
            Log::error($e->getMessage(), $this->exceptionContext($e));
        })->stop();

        parent::register();
    }
}
