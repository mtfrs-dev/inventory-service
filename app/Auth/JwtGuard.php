<?php

namespace App\Auth;

use App\Exceptions\InvalidTokenException;
use App\Exceptions\TokenRevokedException;
use App\Services\Auth\AuthenticationService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class JwtGuard implements Guard
{
    private bool $resolved = false;
    private ?AuthenticatedUser $user = null;

    public function __construct(
        private readonly AuthenticationService $authservice,
        private readonly Request $request,
    ) {
    }

    public function user(): ?Authenticatable
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;

        $payload = $this->request->attributes->get('jwt_payload');

        if ($payload === null) {
            try {
                $payload = $this->authservice->authenticate($this->request);
                $this->request->attributes->set('jwt_payload', $payload);
            } catch (InvalidTokenException|TokenRevokedException) {
                return $this->user = null;
            }
        }

        return $this->user = new AuthenticatedUser($payload);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        // Local credential validation never happens in this service.
        return false;
    }

    public function hasUser(): bool
    {
        return $this->resolved && $this->user !== null;
    }

    public function setUser(Authenticatable $user): void
    {
        if (! $user instanceof AuthenticatedUser) {
            throw new \InvalidArgumentException('JwtGuard only accepts AuthenticatedUser instances.');
        }

        $this->resolved = true;
        $this->user = $user;
    }
}
