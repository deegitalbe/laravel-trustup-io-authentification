<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Deegitalbe\LaravelTrustupIoAuthentification\Commands\LaravelTrustupIoAuthentificationCommand;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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

        require_once __DIR__.'/helpers.php';
    }

    public function packageBooted()
    {
        Auth::provider('trustup.io.provider', function ($app) {
            return $app->make(TrustupIoUserProvider::class);
        });

        Auth::extend('trustup.io', function ($app) {
            return $app->make(TrustupIoUserGuard::class);
        });

        $this->app->singleton(TrustupIoUserProvider::class, function ($app) {
            return new TrustupIoUserProvider;
        });

        $this->app->bind(TrustupIoUserContract::class, function () {
            return config('trustup-io-authentification.model');
        });
    }
}
