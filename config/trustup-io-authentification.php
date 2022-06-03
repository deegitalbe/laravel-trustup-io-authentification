<?php

return [

    'url' => env('TRUSTUP_IO_AUTHENTIFICATION_URL', 'https://auth.trustup.io'),

    /**
     * After a successfull authentication, the user will be redirected to this URL.
     */
    'redirect_url' => '/',

    /**
     * Define which roles should be able to access your application.
     * Make sure to use the Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware\TrustUpIoAuthMiddleware
     * without any parameters on your routes for this to work.
     */
    'roles' => [
        'Super Admin'
    ]
];
