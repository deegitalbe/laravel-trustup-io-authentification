<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUser;

class TrustupIoUserProvider implements UserProvider
{

    const COOKIE_KEY = 'trustup_io_user_token';

    public function setTokenInCookie(string $token): void
    {
        Cookie::queue(self::COOKIE_KEY, $token, config('trustup-io-authentification.duration'));
    }

    public function http(array $headers = [])
    {
        return Http::withHeaders(
            array_merge($headers, [
                'X-Server-Authorization' => env('TRUSTUP_SERVER_AUTHORIZATION')
            ])
        )
        ->baseUrl(config('trustup-io-authentification.url').'/api')
        ->acceptJson();
    }

    public function getToken()
    {
        return request()->header('Authorization')
            ? request()->header('Authorization')
            : request()->cookie(self::COOKIE_KEY);
    }

    public function getUser()
    {
        if ( ! $this->getToken() ) {
            return null;
        }

        return $this->retrieveByBearerToken($this->getToken());
    }

    public function makeUser(array $attributes): TrustupIoUserContract
    {
        $userClass = app(TrustupIoUserContract::class);
        return new $userClass($attributes);
    }
    
    public function retrieveById($identifier)
    {
        $response = $this->http()
            ->get('users/'.$identifier);

        if ( ! $response->ok() ) {
            return null;
        }

        $body = $response->json();

        if ( ! $body || ! $body['user'] ) {
            return null;
        }
        
        return $this->makeUser($body['user']);
    }
    
    public function retrieveByBearerToken($token)
    {
        $response = $this->http([
                'Authorization' => $token
            ])
            ->get('user');

        if ( ! $response->ok() ) {
            return null;
        }

        $body = $response->json();

        if ( ! $body || ! $body['user'] ) {
            return null;
        }

        return $this->makeUser($body['user']);
    }
    
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }
    
    public function updateRememberToken(Authenticatable $user, $token)
    {
        return;
    }
    
    public function retrieveByCredentials(array $credentials)
    {
        return null;
    }
    
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }

    public function logout()
    {
        Cookie::queue(Cookie::forget(self::COOKIE_KEY));

        return redirect()->away(
            config('trustup-io-authentification.url').'/logout?callback=' . urlencode(url()->to('/'))
        );
    }

}