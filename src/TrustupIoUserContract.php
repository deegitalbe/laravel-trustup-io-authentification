<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification;

interface TrustupIoUserContract
{
    public static function find(int $id): ?self;

    public function getAuthIdentifierName();

    public function getAuthIdentifier();

    public function getAuthPassword();

    public function getRememberToken();

    public function setRememberToken($value);

    public function getRememberTokenName();

    public function hasRole(string $role): bool;

    public function hasAnyRole(array $roles): bool;

    public function getImpersonatingUserId(): int|string|null;

    public function getImpersonatingUser(): ?TrustupIoUserContract;

    public function setImpersonatingUser(?TrustupIoUserContract $value): self;
}
