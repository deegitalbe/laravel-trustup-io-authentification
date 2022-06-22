<?php

use Illuminate\Support\Facades\Route;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserProvider;

Route::middleware('web')->get('trustup-io/auth/callback', function () {
    app(TrustupIoUserProvider::class)->setTokenInCookie(request()->get('token'));
    
    return redirect()->to(
        request()->get('path', request()->get('amp;path', config('trustup-io-authentification.redirect_url')))
    );
});