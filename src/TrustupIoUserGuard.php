<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;

class TrustupIoUserGuard implements Guard
{
    protected ?Authenticatable $user = null;

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return $this->id() !== null;
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->user ?? app(TrustupIoUserProvider::class)->getUser();
    }

    /**
     * Get the currently authenticated user token.
     *
     * @return string|null
     */
    public function token()
    {
        return app(TrustupIoUserProvider::class)->getToken();
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        return $this->user()?->getAuthIdentifier();
    }

    /**
     * Validate a user's credentials.
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        throw new Exception('Cannot validate credentials. Please use auth.trustup.io instead.');
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        return $this->user !== null;
    }

    /**
     * Set the current user.
     *
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
    }
}
