<?php

namespace App\DTOs;

final class TokenPayloadDTO
{
    public function __construct(
        public readonly string $subject,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly array $roles,
        public readonly array $permissions,
        public readonly array $scopes,
        public readonly string $issuer,
        public readonly array $audience,
        public readonly int $issuedAt,
        public readonly int $expiresAt,
        public readonly string $jwtId,
        public readonly ?string $keyId = null,
    ) {
    }

    public static function fromClaims(array $claims, ?string $kid = null): self
    {
        $audience = $claims['aud'] ?? [];
        if (is_string($audience)) {
            $audience = [$audience];
        }

        $scopes = $claims['scopes'] ?? $claims['scope'] ?? [];
        if (is_string($scopes)) {
            $scopes = preg_split('/\s+/', trim($scopes), -1, PREG_SPLIT_NO_EMPTY);
        }

        return new self(
            subject     : (string) ($claims['sub'] ?? ''),
            name        : $claims['name'] ?? null,
            email       : $claims['email'] ?? null,
            roles       : array_values((array) ($claims['roles'] ?? [])),
            permissions : array_values((array) ($claims['permissions'] ?? [])),
            scopes      : array_values((array) $scopes),
            issuer      : (string) ($claims['iss'] ?? ''),
            audience    : array_values((array) $audience),
            issuedAt    : (int) ($claims['iat'] ?? 0),
            expiresAt   : (int) ($claims['exp'] ?? 0),
            jwtId       : (string) ($claims['jti'] ?? ''),
            keyId       : $kid,
        );
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        return count(array_intersect($roles, $this->roles)) > 0;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }

    public function toArray(): array
    {
        return [
            'sub'           => $this->subject,
            'name'          => $this->name,
            'email'         => $this->email,
            'roles'         => $this->roles,
            'permissions'   => $this->permissions,
            'scopes'        => $this->scopes,
            'iss'           => $this->issuer,
            'aud'           => $this->audience,
            'iat'           => $this->issuedAt,
            'exp'           => $this->expiresAt,
            'jti'           => $this->jwtId,
            'kid'           => $this->keyId,
        ];
    }
}
