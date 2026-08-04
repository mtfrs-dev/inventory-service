<?php

namespace App\Services\Jwt;

use App\DTOs\TokenPayloadDTO;
use App\Exceptions\InvalidTokenException;
use Illuminate\Support\Facades\Http;

/**
 * Optional synchronous fallback used only when the JWKS endpoint is
 * transiently unreachable. Gated by jwt.revocation.introspection_fallback.
 */
class TokenIntrospectionService
{
    public function introspect(string $token): TokenPayloadDTO
    {
        $response = Http::asForm()
            ->timeout(5)
            ->post(config('jwt.revocation.introspection_uri'), [
                'token' => $token,
            ]);

        if (! $response->successful()) {
            throw new InvalidTokenException('Token introspection failed.');
        }

        $body = $response->json();

        if (empty($body['active'])) {
            throw new InvalidTokenException('The access token is not active.');
        }

        return TokenPayloadDTO::fromClaims($body);
    }
}
