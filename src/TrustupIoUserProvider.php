<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Deegitalbe\LaravelTrustupIoAuthentification\Exceptions\AuthServerError;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class TrustupIoUserProvider implements UserProvider
{
    const COOKIE_KEY = 'trustup_io_user_token';

    public ?TrustupIoUserContract $user = null;

    public ?TrustupIoUserContract $impersonatingUser = null;

    public function getCacheKey(string $id): string
    {
        return 'trustup-io-user-cached-'.$id;
    }

    public function getImpersonatingCacheKey(string $id): string
    {
        return 'trustup-io-impersonating-user-cached-'.$id;
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
        $baseUrl = $this->getBaseUrl();

        $http = Http::withHeaders(
            array_merge($headers, [
                'X-Server-Authorization' => env('TRUSTUP_SERVER_AUTHORIZATION'),
            ])
        )
            ->baseUrl("$baseUrl/api")
            ->acceptJson();

        if (env('APP_ENV') !== 'production') {
            $http->withoutVerifying();
        }

        return $http;
    }

    public function getToken()
    {
        return request()->header('Authorization')
            ? request()->header('Authorization')
            : request()->cookie(self::COOKIE_KEY);
    }

    public function getImpersonatingToken()
    {
        return request()->header('X-Impersonating-Token');
    }

    public function hasImpersonatingToken()
    {
        return request()->hasHeader('X-Impersonating-Token');
    }

    public function getUser()
    {
        if (! $token = $this->getToken()) {
            return null;
        }

        $user = $this->retrieveByBearerToken($token);
        if (! $user) {
            return null;
        }

        if ($this->hasImpersonatingToken()) {
            $impersonatingUser = $this->retrieveImpersonatingByBearerToken(
                $this->getImpersonatingToken()
            );

            $user->setImpersonatingUser($impersonatingUser);
        }

        return $user;
    }

    public function makeUser(array $attributes)
    {
        if (config('trustup-io-authentification.eloquent_model')) {
            $modelClass = config('trustup-io-authentification.eloquent_model.namespace');

            return $modelClass::query()
                ->where(
                    config('trustup-io-authentification.eloquent_model.column'),
                    $attributes['id']
                )
                ->firstOrFail();
        }

        $userClass = app(TrustupIoUserContract::class);

        return new $userClass($attributes);
    }

    public function setUser($user, string $identifier)
    {
        if ($this->cacheEnabled()) {
            Cache::put($this->getCacheKey($identifier), $user, now()->addMinutes(config('trustup-io-authentification.cache.duration')));
        }

        $this->user = $user;

        return $this->user;
    }

    public function setImpersonatingUser($user, string $identifier)
    {
        if ($this->cacheEnabled()) {
            Cache::put($this->getImpersonatingCacheKey($identifier), $user, now()->addMinutes(config('trustup-io-authentification.cache.duration')));
        }

        $this->impersonatingUser = $user;

        return $this->impersonatingUser;
    }

    public function retrieveById($identifier)
    {
        if ($this->isHavingCachedUser($identifier)) {
            return $this->getCachedUser($identifier);
        }

        $response = $this->http()
            ->get('users/'.$identifier);

        // Server error.
        if ($response->serverError()) {
            return $this->handleAuthServerError($identifier, $response);
        }

        if (! $response->ok()) {
            return null;
        }

        $body = $response->json();

        if (! $body || ! $body['user']) {
            return null;
        }

        $user = $this->makeUser($body['user']);

        return $this->setUser($user, $identifier);
    }

    public function retrieveByBearerToken($token)
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->isHavingCachedUser($token)) {
            return $this->getCachedUser($token);
        }

        $user = $this->getUserByBearerToken($token);

        return $this->setUser($user, $token);
    }

    public function retrieveImpersonatingByBearerToken($token)
    {
        if ($this->impersonatingUser) {
            return $this->impersonatingUser;
        }

        if ($this->isHavingCachedImpersonatingUser($token)) {
            return $this->getCachedImpersonatingUser($token);
        }

        $impersonatingUser = $this->getUserByBearerToken($token);

        return $this->setImpersonatingUser($impersonatingUser, $token);
    }

    protected function getUserByBearerToken($token)
    {
        $response = $this->http([
            'Authorization' => $token,
        ])
            ->get('user');

        // Server error.
        if ($response->serverError()) {
            return $this->handleAuthServerError($token, $response);
        }

        // Invalid token.
        if (! $response->ok()) {
            return $this->handleInvalidToken($token);
        }

        $body = $response->json();

        if (! $body || ! $body['user']) {
            return null;
        }

        return $this->makeUser($body['user'], $token);
    }

    /**
     * Happening when auth server is not responsding correctly.
     *
     * @param  string|null  $identifier
     * @param  Response  $response
     * @return null
     */
    protected function handleAuthServerError($identifier, $response)
    {
        if ($this->isUsingCookie($identifier)) {
            $this->forgetCookie();
        }

        report(
            (new AuthServerError())
                ->setResponse($response)
                ->setIdentifier($identifier)
        );

        return null;
    }

    /**
     * Happening when given token is incorrect.
     *
     * @param  string|null  $token
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
     * @param  string|null  $token
     */
    public function isUsingCookie($token): bool
    {
        return request()->cookie(self::COOKIE_KEY, false) === $token;
    }

    /**
     * Telling if having cached user corresponding to token
     *
     * @param  string|null  $token
     */
    public function isHavingCachedUser($token): bool
    {
        return $this->cacheEnabled() && Cache::has($this->getCacheKey($token));
    }

    /**
     * Telling if having cached user corresponding to token
     *
     * @param  string|null  $token
     */
    public function isHavingCachedImpersonatingUser($token): bool
    {
        return $this->cacheEnabled() && Cache::has($this->getImpersonatingCacheKey($token));
    }

    /**
     * Forgetting cached user matching token.
     *
     * @param  string|null  $token
     */
    public function forgetCachedUser($token): void
    {
        if (! $this->cacheEnabled()) {
            return;
        }

        Cache::forget($this->getCacheKey($token));
    }

    /**
     * Forgetting cached user matching token.
     *
     * @param  string|null  $token
     */
    public function forgetCachedImpersonatingUser($token): void
    {
        if (! $this->cacheEnabled()) {
            return;
        }

        Cache::forget($this->getImpersonatingCacheKey($token));
    }

    /**
     * Forgetting cached user matching token.
     *
     * @param  string|null  $token
     * @return TrustupIoUserContract|null
     */
    public function getCachedUser($token)
    {
        if (! $this->cacheEnabled()) {
            return null;
        }

        return Cache::get($this->getCacheKey($token));
    }

    public function getCachedImpersonatingUser($token)
    {
        if (! $this->cacheEnabled()) {
            return null;
        }

        return Cache::get($this->getImpersonatingCacheKey($token));
    }

    // faire une méthode qui permet de supprimer le cache de l'utilisateur

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
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
            config('trustup-io-authentification.url').'/logout?callback='.urlencode(url()->to('/'))
        );
    }

    /**
     * Docker compatible url.
     *
     * Docker is unable to make server to server calls using "https://xxxx".
     * We have to use service name if docker is activated in configuration.
     */
    protected function getBaseUrl(): string
    {
        return get_trustup_io_authentification_base_url();
    }
}
