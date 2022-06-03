<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Illuminate\Support\Facades\Auth;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserGuard;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserProvider;
use Deegitalbe\LaravelTrustupIoAuthentification\Commands\LaravelTrustupIoAuthentificationCommand;

class LaravelTrustupIoAuthentificationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-trustup-io-authentification')
            ->hasConfigFile()
            ->hasRoute('callback');
            // ->hasViews()
            // ->hasMigration('create_laravel-trustup-io-authentification_table')
            // ->hasCommand(LaravelTrustupIoAuthentificationCommand::class)
    }
    
    public function packageBooted()
    {
        Auth::provider('trustup.io.provider', function ($app) {
            return $app->make(TrustupIoUserProvider::class);
        });

        Auth::extend('trustup.io', function ($app) {
            return $app->make(TrustupIoUserGuard::class);
        });
    }
}
