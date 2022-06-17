<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUser;
use Exception;

class TrustupIoUserProvider implements UserProvider
{

    const COOKIE_KEY = 'trustup_io_user_token';

    public ?TrustupIoUserContract $user = null;

    public function getCacheKey(string $id): string
    {
        return 'trustup-io-user-cached-'.$id;
    }

    public function cacheEnabled(): bool
    {
        return config('trustup-io-authentification.cache.enabled');
    }

    public function setTokenInCookie(string $token): void
    {
        Cookie::queue(self::COOKIE_KEY, $token, config('trustup-io-authentification.duration'));
    }

    public function http(array $headers = [])
    {
        $http = Http::withHeaders(
            array_merge($headers, [
                'X-Server-Authorization' => env('TRUSTUP_SERVER_AUTHORIZATION')
            ])
        )
        ->baseUrl(config('trustup-io-authentification.url').'/api')
        ->acceptJson();

        if (env('APP_ENV') !== "production"):
            $http->withoutVerifying();
        endif;

        return $http;
    }

    public function getToken()
    {
        return request()->header('Authorization')
            ? request()->header('Authorization')
            : request()->cookie(self::COOKIE_KEY);
    }

    public function getUser()
    {
        if ( ! $token = $this->getToken() ) {
            return null;
        }

        return $this->retrieveByBearerToken($token);
    }

    public function makeUser(array $attributes, string $identifier)
    {
        if ( config('trustup-io-authentification.eloquent_model') ) {
            $modelClass = config('trustup-io-authentification.eloquent_model.namespace');
            return $this->setUser( $modelClass::where(config('trustup-io-authentification.eloquent_model.column'), $attributes['id'])->firstOrFail(), $identifier );
        }

        $userClass = app(TrustupIoUserContract::class);
        return $this->setUser( new $userClass($attributes), $identifier );
    }

    public function setUser($user, string $identifier)
    {
        if ( $this->cacheEnabled() ) {
            Cache::put( $this->getCacheKey($identifier), $user, now()->addMinutes( config('trustup-io-authentification.cache.duration') ) );
        }
        
        $this->user = $user;
        return $this->user;
    }

    public function retrieveById($identifier)
    {
        if ( $this->cacheEnabled() && Cache::has( $this->getCacheKey($identifier)) ) {
            return Cache::get( $this->getCacheKey($identifier));
        }

        $response = $this->http()
            ->get('users/'.$identifier);

        if ( ! $response->ok() ) {
            return null;
        }

        $body = $response->json();

        if ( ! $body || ! $body['user'] ) {
            return null;
        }
        
        return $this->makeUser($body['user'], $identifier);
    }
    
    public function retrieveByBearerToken($token)
    {
        if ( $this->user ) {
            return $this->user;
        }

        if ( $this->cacheEnabled() && Cache::has( $this->getCacheKey($token) )) {
            return Cache::get( $this->getCacheKey($token) );
        }

        $response = $this->http([
                'Authorization' => $token
            ])
            ->get('user');

        if ( ! $response->ok() ) {
            report(new Exception('Could not retrieve user from API via token.'));
            return null;
        }

        $body = $response->json();

        if ( ! $body || ! $body['user'] ) {
            return null;
        }

        return $this->makeUser($body['user'], $token);
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