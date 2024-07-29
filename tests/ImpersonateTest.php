<?php

use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUser;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserProvider;

beforeEach(function () {
    $this->userService = Mockery::mock(TrustupIoUserProvider::class)->makePartial();
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
    $impersonatingUser->shouldReceive('getAttribute')->andReturn(2);

    $user->shouldReceive('setImpersonatingUserId')->with(2);

    $this->userService->shouldReceive('getToken')->andReturn($token);
    $this->userService->shouldReceive('retrieveByBearerToken')->with($token)->andReturn($user);
    $this->userService->shouldReceive('getImpersonatingToken')->andReturn($impersonateToken);
    $this->userService->shouldReceive('retrieveImpersonatingByBearerToken')->with($impersonateToken, $user)->andReturn($impersonatingUser);

    $result = $this->userService->getUser();
    expect($result)->toBe($user);
    $user->shouldHaveReceived('setImpersonatingUserId')->with(2);
});

test('getUser returns null when impersonating user has no valid role', function () {
    $token = 'valid-token';
    $impersonateToken = 'impersonate-token';
    $user = Mockery::mock(TrustupIoUser::class);
    $impersonatingUser = Mockery::mock(TrustupIoUser::class);

    // Mock behavior for impersonating user
    $impersonatingUser->shouldReceive('hasAnyRole')->andReturn(false);

    // Mock behavior for UserService
    $this->userService->shouldReceive('getToken')->andReturn($token);
    $this->userService->shouldReceive('retrieveByBearerToken')->with($token)->andReturn($user);
    $this->userService->shouldReceive('getImpersonatingToken')->andReturn($impersonateToken);
    $this->userService->shouldReceive('retrieveImpersonatingByBearerToken')->with($impersonateToken, $user)->andReturn($impersonatingUser);

    // Call the method and verify the result
    $result = $this->userService->getUser();
    $this->assertNull($result);
});

