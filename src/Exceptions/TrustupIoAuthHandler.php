<?php
namespace Deegitalbe\LaravelTrustupIoAuthentification\Exceptions;

use Deegitalbe\LaravelTrustupIoAuthentification\Contracts\Exceptions\SkipAuthContextContract;
use Henrotaym\LaravelFlareExceptionHandler\Context\FlareContext;
use Henrotaym\LaravelFlareExceptionHandler\FlareExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Facades\Log;

class TrustupIoAuthHandler extends Handler
{
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(FlareContext::report());
        $this->reportable(function (SkipAuthContextContract $e) {
            Log::error($e->getMessage(), $this->exceptionContext($e));
        })->stop();

        parent::register();
    }
}