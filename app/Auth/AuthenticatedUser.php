<?php

namespace App\Auth;

use App\DTOs\TokenPayloadDTO;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;

class AuthenticatedUser implements Authenticatable, AuthorizableContract
{
    use Authorizable;

    private bool $localRecordResolved = false;
    private ?User $localRecord = null;

    public function __construct(private readonly TokenPayloadDTO $token)
    {
    }

    public function token(): TokenPayloadDTO
    {
        return $this->token;
    }

    // public function localRecord(): ?User
    // {
    //     if (! $this->localRecordResolved) {
    //         $this->localRecord = User::where('external_id', $this->token->subject)->first();
    //         $this->localRecordResolved = true;
    //     }

    //     return $this->localRecord;
    // }

    public function hasRole(string $role): bool
    {
        return $this->token->hasRole($role);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->token->hasAnyRole($roles);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->token->hasPermission($permission);
    }

    public function hasScope(string $scope): bool
    {
        return $this->token->hasScope($scope);
    }

    public function getAuthIdentifierName(): string
    {
        return 'sub';
    }

    public function getAuthIdentifier(): string
    {
        return $this->token->subject;
    }

    public function getAuthPasswordName(): string
    {
        return '';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // No-op: this is a stateless resource server, there is no remember-me.
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
