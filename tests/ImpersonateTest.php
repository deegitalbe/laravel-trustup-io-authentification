<?php

use Deegitalbe\LaravelTrustupIoAuthentification\Http\Middleware\TrustUpIoAuthMiddleware;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUser;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserProvider;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->userService = Mockery::mock(TrustupIoUserProvider::class)->makePartial();
    app()->bind(TrustupIoUserProvider::class, fn () => $this->userService);
});

test('getUser returns user when token is valid', function () {
    $token = 'valid-token';
    $user = Mockery::mock(TrustupIoUser::class);
    $this->userService->shouldReceive('getToken')->andReturn($token);
    $this->userService->shouldReceive('retrieveByBearerToken')->with($token)->andReturn($user);
    $this->userService->shouldReceive('getImpersonatingToken')->andReturn(null);

    $result = $this->userService->getUser();
    expect($result)->toBe($user);
});

test('getUser returns null when token is invalid', function () {
    $this->userService->shouldReceive('getToken')->andReturn(null);

    $result = $this->userService->getUser();
    expect($result)->toBeNull();
});

test('getUser returns user with impersonation', function () {
    $token = 'valid-token';
    $impersonateToken = 'impersonate-token';
    $user = Mockery::mock(TrustupIoUser::class);
    $impersonatingUser = Mockery::mock(TrustupIoUser::class);

    $impersonatingUser->shouldReceive('hasAnyRole')->andReturn(true);

    $user->shouldReceive('setImpersonatingUser')->with($impersonatingUser);

    $this->userService->shouldReceive('getToken')->andReturn($token);
    $this->userService->shouldReceive('retrieveByBearerToken')->with($token)->andReturn($user);
    $this->userService->shouldReceive('getImpersonatingToken')->andReturn($impersonateToken);
    $this->userService->shouldReceive('retrieveImpersonatingByBearerToken')->with($impersonateToken)->andReturn($impersonatingUser);

    $result = $this->userService->getUser();
    expect($result)->toBe($user);
});

test('middleware accept request to passthrough without impersonation', function () {
    config([
        'auth.guards' => [
            'api' => [
                'driver' => 'trustup.io',
            ],
        ],
    ]);
    $token = 'valid-token';
    $user = Mockery::mock(TrustupIoUser::class)->makePartial();
    $user->expects('getAuthIdentifier')->with()->andReturn(1);
    $user->expects('hasAnyRole')->with(config('trustup-io-authentification.roles'))->andReturn(true);

    // Mock behavior for UserService
    $this->userService->shouldReceive('getToken')->andReturn($token);
    $this->userService->shouldReceive('hasImpersonatingToken')->with()->andReturn(false);
    $this->userService->shouldReceive('retrieveByBearerToken')->with($token)->andReturn($user);

    // Call the method and verify the result
    $request = request();
    $request->headers->set('Accept', 'application/json');
    $response = new TestResponse(app()->make(TrustUpIoAuthMiddleware::class)->handle(
        $request,
        fn () => response(['message' => 'nice']),
        null,
        'api'
    ));
    $response->assertOk();
});

test('middleware accept request to passthrough with correct tokens', function () {
    config([
        'auth.guards' => [
            'api' => [
                'driver' => 'trustup.io',
            ],
        ],
    ]);
    $token = 'valid-token';
    $impersonateToken = 'impersonate-token';
    $user = Mockery::mock(TrustupIoUser::class)->makePartial();
    $impersonatingUser = Mockery::mock(TrustupIoUser::class);
    $user->expects('getAuthIdentifier')->with()->andReturn(1);
    $user->expects('hasAnyRole')->with(config('trustup-io-authentification.roles'))->andReturn(true);
    $impersonatingUser->expects('hasAnyRole')->with(config('trustup-io-authentification.impersonating_roles'))->andReturn(true);

    // Mock behavior for UserService
    $this->userService->shouldReceive('getToken')->andReturn($token);
    $this->userService->shouldReceive('hasImpersonatingToken')->with()->andReturn(true);
    $this->userService->shouldReceive('retrieveByBearerToken')->with($token)->andReturn($user);
    $this->userService->shouldReceive('retrieveImpersonatingByBearerToken')->with($impersonateToken)->andReturn($impersonatingUser);

    // Call the method and verify the result
    $request = request();
    $request->headers->set('X-Impersonating-Token', $impersonateToken);
    $request->headers->set('Accept', 'application/json');
    $response = new TestResponse(app()->make(TrustUpIoAuthMiddleware::class)->handle(
        $request,
        fn () => response(['message' => 'nice']),
        null,
        'api'
    ));
    $response->assertOk();
});

test('middleware returns 403 for request that has impersonate user with invalid roles', function () {
    config([
        'auth.guards' => [
            'api' => [
                'driver' => 'trustup.io',
            ],
        ],
    ]);
    $token = 'valid-token';
    $impersonateToken = 'impersonate-token';
    $user = Mockery::mock(TrustupIoUser::class)->makePartial();
    $impersonatingUser = Mockery::mock(TrustupIoUser::class);

    $user->expects('getAuthIdentifier')->with()->andReturn(1);
    $user->expects('hasAnyRole')->with(config('trustup-io-authentification.roles'))->andReturn(true);

    $impersonatingUser->expects('hasAnyRole')->with(config('trustup-io-authentification.impersonating_roles'))->andReturn(false);

    // Mock behavior for UserService
    $this->userService->shouldReceive('getToken')->andReturn($token);
    $this->userService->shouldReceive('hasImpersonatingToken')->with()->andReturn(true);
    $this->userService->shouldReceive('retrieveByBearerToken')->with($token)->andReturn($user);
    $this->userService->shouldReceive('retrieveImpersonatingByBearerToken')->with($impersonateToken)->andReturn($impersonatingUser);

    // Call the method and verify the result
    $request = request();
    $request->headers->set('X-Impersonating-Token', $impersonateToken);
    $request->headers->set('Accept', 'application/json');

    $response = new TestResponse(app()->make(TrustUpIoAuthMiddleware::class)->handle(
        $request,
        fn () => response(['message' => 'nice']),
        null,
        'api'
    ));
    $response->assertStatus(403)
        ->assertJson(['message' => 'Invalid impersonation']);
});

test('middleware returns 403 for request that has invalid impersonate token', function () {
    config([
        'auth.guards' => [
            'api' => [
                'driver' => 'trustup.io',
            ],
        ],
    ]);
    $token = 'valid-token';
    $impersonateToken = 'impersonate-token';
    $user = Mockery::mock(TrustupIoUser::class)->makePartial();

    $user->expects('getAuthIdentifier')->with()->andReturn(1);
    $user->expects('hasAnyRole')->with(config('trustup-io-authentification.roles'))->andReturn(true);

    // $impersonatingUser->expects('hasAnyRole')->with(config('trustup-io-authentification.impersonating_roles'))->andReturn(false);

    // Mock behavior for UserService
    $this->userService->shouldReceive('getToken')->andReturn($token);
    $this->userService->shouldReceive('hasImpersonatingToken')->with()->andReturn(true);
    $this->userService->shouldReceive('retrieveByBearerToken')->with($token)->andReturn($user);
    $this->userService->shouldReceive('retrieveImpersonatingByBearerToken')->with($impersonateToken)->andReturn(null);

    // Call the method and verify the result
    $request = request();
    $request->headers->set('X-Impersonating-Token', $impersonateToken);
    $request->headers->set('Accept', 'application/json');

    $response = new TestResponse(app()->make(TrustUpIoAuthMiddleware::class)->handle(
        $request,
        fn () => response(['message' => 'nice']),
        null,
        'api'
    ));
    $response->assertStatus(403)
        ->assertJson(['message' => 'Invalid impersonation']);
});
