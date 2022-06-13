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
     * Are you accessing trustup's database directly, and have relations on the users table ?
     * Then you can define your own Eloquent model here, and which column should be used to query it based on the ID of the authed user.
     * auth()->user() will return that Eloquent model or throw an exception if not found.
     * 
     * Please provide a namespace and a column attribute, like this:
     * 'eloquent_model' => [
     *   'namespace' => \Modules\User\Entities\User::class,
     *   'column' => 'id'
     * ],
     */
    'eloquent_model' => null,

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
