<?php

use Illuminate\Support\Facades\Route;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserProvider;

Route::get('trustup-io/auth/callback', function () {
    app(TrustupIoUserProvider::class)->setTokenInSession(request()->get('token'));
    return redirect()->to(
        config('trustup-io-authentification.redirect_url')
    );
});