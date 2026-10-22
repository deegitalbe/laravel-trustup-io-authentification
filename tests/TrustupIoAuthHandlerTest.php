<?php

use Deegitalbe\LaravelTrustupIoAuthentification\Exceptions\AuthServerError;
use Deegitalbe\LaravelTrustupIoAuthentification\Exceptions\TrustupIoAuthHandler;
use Henrotaym\LaravelFlareExceptionHandler\FlareExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelIgnition\Facades\Flare;

beforeEach(function () {
    app()->singleton(ExceptionHandler::class, TrustupIoAuthHandler::class);
});

function authServerError(): AuthServerError
{
    return (new AuthServerError)
        ->setIdentifier('user-identifier')
        ->setResponse(new Response(new GuzzleHttp\Psr7\Response(401, [], '{}')));
}

test('exception handler is the package handler', function () {
    $handler = app(ExceptionHandler::class);

    expect($handler)->toBeInstanceOf(TrustupIoAuthHandler::class)
        ->and($handler)->toBeInstanceOf(FlareExceptionHandler::class);
});

test('handler renders a thrown exception as a 500 response', function () {
    Route::get('/handler-failure', fn () => throw new RuntimeException('boom'));

    $this->get('/handler-failure')->assertStatus(500);
});

test('auth server error is logged once with its context', function () {
    Flare::shouldReceive('context')->twice();
    Log::shouldReceive('error')
        ->once()
        ->with('Auth server could not authenticate user.', Mockery::on(
            fn (array $context) => $context['identifier'] === 'user-identifier'
                && $context['response']['status'] === 401
        ));

    app(ExceptionHandler::class)->report(authServerError());
});

test('auth server error context is sent to flare', function () {
    Log::shouldReceive('error')->once();
    Flare::shouldReceive('context')->once()->with('identifier', 'user-identifier');
    Flare::shouldReceive('context')->once()->with('response', Mockery::on(
        fn (array $response) => $response['status'] === 401
    ));

    app(ExceptionHandler::class)->report(authServerError());
});

test('plain exception is not logged as an auth server error', function () {
    Log::shouldReceive('error')
        ->withArgs(fn ($message) => $message === 'Auth server could not authenticate user.')
        ->never();
    Log::shouldReceive('error')->withAnyArgs()->zeroOrMoreTimes();

    app(ExceptionHandler::class)->report(new RuntimeException('boom'));
});
