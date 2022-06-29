<?php
namespace Deegitalbe\LaravelTrustupIoAuthentification\Exceptions;

use Deegitalbe\LaravelTrustupIoAuthentification\Contracts\Exceptions\SkipAuthContextContract;
use Henrotaym\LaravelFlareExceptionHandler\FlareExceptionHandler;
use Illuminate\Support\Facades\Log;

class TrustupIoAuthExceptionHandler extends FlareExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (SkipAuthContextContract $e) {
            $this->reportContextToFlare($e);
            Log::error($e->getMessage(), $this->exceptionContext($e));
        })->stop();

        parent::register();
    }
}