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
     * Which guard are you using to authenticate your users.
     * Default value if "null" which results to auth()->user().
     * Are you using two different guards? You can define it on the Middleware level as second parameter.
     * Ex: TrustUpIoAuthMiddleware::class.':Super Admin|Translator';
     */
    'guard' => 'null',

    /**
     * Define which roles should be able to access your application.
     * You can override these roles on the Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware\TrustUpIoAuthMiddleware
     * as a first parameter.
     * Want to pass multiple roles to your middleware? Separate your roles with a "|"
     * Ex: TrustUpIoAuthMiddleware::class.':Super Admin|Translator';
     */
    'roles' => [
        'Super Admin'
    ]
];
