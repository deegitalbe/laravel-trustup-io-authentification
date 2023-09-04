[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Connect your Laravel project to our centralized authentication service

[![Latest Version on Packagist](https://img.shields.io/packagist/v/deegitalbe/laravel-trustup-io-authentification.svg?style=flat-square)](https://packagist.org/packages/deegitalbe/laravel-trustup-io-authentification)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/deegitalbe/laravel-trustup-io-authentification/run-tests?label=tests)](https://github.com/deegitalbe/laravel-trustup-io-authentification/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/deegitalbe/laravel-trustup-io-authentification/Check%20&%20fix%20styling?label=code%20style)](https://github.com/deegitalbe/laravel-trustup-io-authentification/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/deegitalbe/laravel-trustup-io-authentification.svg?style=flat-square)](https://packagist.org/packages/deegitalbe/laravel-trustup-io-authentification)

## Installation

### Require package
```bash
composer require deegitalbe/laravel-trustup-io-authentification
```
### Publish config
```bash
php artisan vendor:publish --tag="trustup-io-authentification-config"
```

This will publish `trustup-io-authentification.php` in config folder

### Define roles
You should define roles that have access in config file `trustup-io-authentification.php`.
```php
'roles' => [
    'Super Admin',
    'Employee',
    'Translator'
],
```

### Define guards
In config file `auth.php` redefine your guards
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
        'driver' => 'trustup.io',
    ],
    'api' =>[
        'driver' => 'trustup.io'
    ]
],
```

### Activate docker
In case your application is using docker-integration, define this env variable

```shell
TRUSTUP_IO_AUTH_DOCKER_ACTIVATED=true
```

### Add middleware to protect your restricted routes
```php
use Illuminate\Support\Facades\Route;
use Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware\TrustUpIoAuthMiddleware;

Route::middleware(TrustUpIoAuthMiddleware::class)->group(function() {
    // Your restricted routes ...
});

Route::middleware(TrustUpIoAuthMiddleware::class.':Super Admin|Translator')->group(function() {
    // Your restricted routes only accessible by super admins or translators ...
});
```

## Docker compatibility
Update package to latest version
```bash
composer require deegitalbe/laravel-trustup-io-authentification
```

Force config publication and set correct values (model, roles, guard, ...)
```bash
php artisan vendor:publish --tag="trustup-io-authentification-config" --force
```

Define env variable
```shell
TRUSTUP_IO_AUTH_DOCKER_ACTIVATED=true
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Florian Husquinet](https://github.com/deegitalbe)
- [Henrotay Mathieu](https://github.com/henrotaym)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
