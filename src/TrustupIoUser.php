<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class TrustupIoUser extends Model implements Authenticatable, TrustupIoUserContract
{
    protected $guarded = [];

    protected ?int $impersonatingUserId = null;

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
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the ID for the currently authenticated user that request an impersonate token.
     */
    public function getImpersonatingUserId(): int|string|null
    {
        return $this->impersonatingUserId;
    }

    /**
     * set the ID for the currently authenticated user that request an impersonate token.
     *
     * @return int|string|null
     */
    public function setImpersonatingUserId(int|string|null $id): self
    {
        $this->impersonatingUserId = $id;

        return $this;
    }
}
