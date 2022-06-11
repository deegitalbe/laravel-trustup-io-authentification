<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

class TrustupIoUser implements Authenticatable, TrustupIoUserContract
{

    public function __construct(array $attributes)
    {
        foreach ( $attributes as $key => $attribute ) {
            $this->{$key} = $attribute;
        }
    }

    public static function find(int $id): ?self
    {
        return app(TrustupIoUserProvider::class)->retrieveById($id);
    } 
    
    public function getAuthIdentifierName()
    {
        return 'id';
    }
    
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }
    
    public function getAuthPassword()
    {
        return $this->password;
    }
    
    public function getRememberToken()
    {
        throw new Exception('Cannot get the remember me token.');
    }
    
    public function setRememberToken($value)
    {
        throw new Exception('Cannot set the remember me token.');
    }
    
    public function getRememberTokenName()
    {
        return '';
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ( $roles as $role ) {
            if ( $this->hasRole($role) ) {
                return true;
            }
        }
        return false;
    }
}