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

    public function getUser()
    {
        if ( ! request()->cookie(self::COOKIE_KEY) ) {
            return null;
        }

        return $this->retrieveById(request()->cookie(self::COOKIE_KEY));
    }
    
    public function retrieveById($identifier)
    {
        $response = Http::withHeaders([
                'Authorization' => $identifier
            ])
            ->get(config('trustup-io-authentification.url').'/api/user')
            ->throw()
            ->json();
            
        if ( ! $response['user'] ) {
            return null;
        }
        
        return new TrustupIoUser($response['user']);
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

}