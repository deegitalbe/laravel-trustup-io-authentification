<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Deegitalbe\LaravelTrustupIoAuthentification\TrustupIoUser;

class TrustupIoUserProvider implements UserProvider
{

    const SESSION_KEY = 'trustup.io.user';

    public function setTokenInSession(string $token): void
    {
        session()->put(self::SESSION_KEY, $token);
    }
    
    public function retrieveById($identifier)
    {
        if ( ! session()->get(self::SESSION_KEY) ) {
            return null;
        }

        $response = Http::withHeaders([
                'Authorization' => session()->get(self::SESSION_KEY)
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