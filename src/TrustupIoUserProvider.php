<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUser;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUserContract;
use Deegitalbe\LaravelTrustupIoAuthentification\Exceptions\AuthServerError;

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

        if ( $this->isHavingCachedUser($token) ) {
            return $this->getCachedUser($token);
        }

        $response = $this->http([
                'Authorization' => $token
            ])
            ->get('user');
        
        // Server error.
        if ( $response->serverError() ) {
            return $this->handleAuthServerError($token, $response);
        }

        // Invalid token.
        if ( ! $response->ok() ) {
            return $this->handleInvalidToken($token);
        }

        $body = $response->json();

        if ( ! $body || ! $body['user'] ) {
            return null;
        }

        return $this->makeUser($body['user'], $token);
    }

    /**
     * Happening when auth server is not responsding correctly.
     * 
     * @param string|null $token
     * @param Response $response
     * @return null
     */
    protected function handleAuthServerError($token, $response)
    {
        if ($this->isUsingCookie($token)) {
            $this->forgetCookie();
        }

        report(
            (new AuthServerError())->setResponse($response)
        );

        return null;
    }

    /**
     * Happening when given token is incorrect.
     * 
     * @param string|null $token
     * @return null
     */
    protected function handleInvalidToken($token)
    {
        if ($this->isUsingCookie($token)) {
            $this->forgetCookie();
        }

        return null;
    }

    /**
     * Telling if given token is corresponding to stored cookie.
     * 
     * @param string|null $token
     * @return bool
     */
    public function isUsingCookie($token): bool
    {
        return request()->cookie(self::COOKIE_KEY, false) === $token;
    }

    /**
     * Telling if having cached user corresponding to token
     * 
     * @param string|null $token
     * @return bool
     */
    public function isHavingCachedUser($token): bool
    {
        return $this->cacheEnabled() && Cache::has($this->getCacheKey($token));
    }

    /**
     * Forgetting cached user matching token.
     * 
     * @param string|null $token
     * @return void
     */
    public function forgetCachedUser($token): void
    {
        if (!$this->cacheEnabled()):
            return;
        endif;

        Cache::forget($this->getCacheKey($token));
    }

    /**
     * Forgetting cached user matching token.
     * 
     * @param string|null $token
     * @return TrustupIoUserContract|null
     */
    public function getCachedUser($token)
    {
        if (!$this->cacheEnabled()):
            return null;
        endif;

        Cache::get($this->getCacheKey($token));
    }

    // faire une méthode qui permet de supprimer le cache de l'utilisateur
    
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

    /**
     * Used to forget stored cookie.
     * 
     * @return void
     */
    public function forgetCookie()
    {
        Cookie::queue(Cookie::forget(self::COOKIE_KEY));
    }

    public function logout()
    {
        $this->forgetCookie();

        return redirect()->away(
            config('trustup-io-authentification.url').'/logout?callback=' . urlencode(url()->to('/'))
        );
    }

}