## Installation

You can install the package via composer:

```bash
composer require deegitalbe/laravel-trustup-io-authentification
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-trustup-io-authentification-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="trustup-io-authentification-config"
```

You can change in your config/auth.php file this:

```php
'guards' => [
        'web' => [
            'driver' => 'trustup.io',
        ],
        'api' =>[
            'driver' => 'trustup.io'
        ]
    ],
```

Now you can add the middleware on your route:

```php
Route::middleware(TrustUpIoAuthMiddleware::class)
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-trustup-io-authentification-views"
```



## Usage

```php
$laravelTrustupIoAuthentification = new Deegitalbe\LaravelTrustupIoAuthentification();
echo $laravelTrustupIoAuthentification->echoPhrase('Hello, Deegitalbe!');
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
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
