<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Cache;

/**
 * Redis-backed denylist for revoked tokens, plus a per-subject "tombstone"
 * supporting revoke-all-sessions semantics. Populated exclusively by
 * AuthWebhookController in response to signed Auth Service webhooks.
 */
class RevocationService
{
    private string $store;
    private string $prefix;

    public function __construct()
    {
        $this->store = config('jwt.revocation.store', 'redis');
        $this->prefix = config('jwt.revocation.denylist_prefix', 'jwt:revoked:');
    }

    public function isRevoked(string $jti): bool
    {
        if (! config('jwt.revocation.enabled', true)) {
            return false;
        }

        return Cache::store($this->store)->has($this->prefix.$jti);
    }

    public function revoke(string $jti, int $ttlSeconds): void
    {
        Cache::store($this->store)->put($this->prefix.$jti, true, $ttlSeconds);
    }

    public function revokeAllForUser(string $subject, int $ttlSeconds): void
    {
        Cache::store($this->store)->put(
            $this->prefix.'user:'.$subject,
            now()->timestamp,
            $ttlSeconds
        );
    }

    public function isRevokedForUser(string $subject, int $issuedAt): bool
    {
        if (! config('jwt.revocation.enabled', true)) {
            return false;
        }

        $tombstone = Cache::store($this->store)->get($this->prefix.'user:'.$subject);

        if ($tombstone === null) {
            return false;
        }

        return $issuedAt <= (int) $tombstone;
    }
}
