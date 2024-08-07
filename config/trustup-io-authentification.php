<?php

return [

    'url' => env(
        'TRUSTUP_IO_AUTH_URL',
        env('TRUSTUP_IO_AUTHENTIFICATION_URL', 'https://auth.trustup.io')
    ),

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
    'guard' => null,

    /**
     * Define which roles should be able to access your application.
     * You can override these roles on the Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware\TrustUpIoAuthMiddleware
     * as a first parameter.
     * Want to pass multiple roles to your middleware? Separate your roles with a "|"
     * Ex: TrustUpIoAuthMiddleware::class.':Super Admin|Translator';
     */
    'roles' => [
        'Super Admin',
        'Worksite Admin',
        'Developer',
    ],

    /**
     * Define which roles should be able to make an impersonation in your application.
     */
    'impersonating_roles' => [
        'Super Admin',
        'Worksite Admin',
        'Developer',
    ],

    /**
     * To improve performance and not constantly query the API to retrieve the logged in user (or another one)
     * you can enable the cache and set a duration (in minutes).
     * Each user retrieved from the API will be stored in cache for that duration with the trustup-io-user-cached-{id} key.
     *
     * Note: To retrieve the logged in user from the cache, your application will store the token has cache key.
     * This might be a lot to store in cache, so don't include a duration too long.
     * Also note that with a longer cache, you risk having a potentially logged out user to keep accessing your application.
     */
    'cache' => [
        'enabled' => true,
        'duration' => 5,
    ],

    /**
     * Docker related config.
     *
     * Define env variable TRUSTUP_IO_AUTH_DOCKER_ACTIVATED=1 to active docker integration.
     */
    'docker' => [
        'service' => 'trustup-io-auth',
        'activated' => env('TRUSTUP_IO_AUTH_DOCKER_ACTIVATED', false),
    ],
];
