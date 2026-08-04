<?php

namespace App\Services\Jwt;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class JwksService
{
    public function getKeySet(): array
    {
        $ttl = (int) config('jwt.jwks.cache_ttl', 21600);

        return Cache::remember(
            config('jwt.jwks.cache_key', 'auth_service.jwks'),
            $ttl,
            fn () => $this->fetchFromAuthService()
        );
    }

    public function getKeyForKeyId(string $kid): array
    {
        $keys = $this->getKeySet();
        $key = $this->findKey($keys, $kid);

        if ($key !== null) {
            return $key;
        }

        // Possible rotation in progress — force a single refresh and retry.
        $keys = $this->forceRefresh();
        $key = $this->findKey($keys, $kid);

        if ($key === null) {
            throw new RuntimeException("Unable to resolve signing key for kid [{$kid}].");
        }

        return $key;
    }

    public function forceRefresh(): array
    {
        $cacheKey = config('jwt.jwks.cache_key', 'auth_service.jwks');

        Cache::forget($cacheKey);

        return Cache::lock('jwks:refresh:lock', 10)->block(5, function () use ($cacheKey) {
            $ttl = (int) config('jwt.jwks.cache_ttl', 3600);

            return Cache::remember($cacheKey, $ttl, fn () => $this->fetchFromAuthService());
        });
    }

    private function findKey(array $keys, string $kid): ?array
    {
        foreach ($keys['keys'] ?? [] as $key) {
            if (($key['kid'] ?? null) === $kid) {
                return $key;
            }
        }

        return null;
    }

    private function fetchFromAuthService(): array
    {
        $uri        = config('jwt.jwks.uri');
        $timeout    = (int) config('jwt.jwks.http_timeout', 5);

        try {
            $response = Http::timeout($timeout)
                ->retry(2, 200)
                ->acceptJson()
                ->get($uri);
        } catch (\Throwable $e) {
            Log::error('JWKS fetch failed', ['uri' => $uri, 'exception' => $e->getMessage()]);
            throw new RuntimeException('Unable to fetch JWKS from Auth Service.', 0, $e);
        }

        if (! $response->successful()) {
            Log::error('JWKS fetch returned non-success status', ['uri' => $uri, 'status' => $response->status()]);
            throw new RuntimeException('Auth Service JWKS endpoint returned an error.');
        }

        $body = $response->json();

        if (! is_array($body) || ! isset($body['keys']) || ! is_array($body['keys'])) {
            Log::error('JWKS response malformed', ['uri' => $uri]);
            throw new RuntimeException('Auth Service JWKS response is malformed.');
        }

        return $body;
    }
}
