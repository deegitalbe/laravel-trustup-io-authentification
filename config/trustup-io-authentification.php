<?php

return [

    'url' => env('TRUSTUP_IO_AUTHENTIFICATION_URL', 'https://auth.trustup.io'),

    /**
     * After a successfull authentication, the user will be redirected to this URL.
     */
    'redirect_url' => '/',

    /**
     * Which model to user when calling auth()->user().
     * Default value is Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUser.
     * Makes sure your model either extends the default value, or implements the Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserContract.
     */
    'model' => \Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUser::class,

    /**
     * Define which roles should be able to access your application.
     * Make sure to use the Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware\TrustUpIoAuthMiddleware
     * without any parameters on your routes for this to work.
     */
    'roles' => [
        'Super Admin'
    ]
];
