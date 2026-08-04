<?php

namespace App\Services\Auth;

use App\DTOs\TokenPayloadDTO;
use App\Exceptions\InvalidTokenException;
use App\Exceptions\TokenRevokedException;
use App\Services\Auth\RevocationService;
use App\Services\Jwt\JwtValidator;
use App\Services\Jwt\TokenIntrospectionService;
use Illuminate\Http\Request;
use RuntimeException;

class AuthenticationService
{
    public function __construct(
        private readonly JwtValidator $validator,
        private readonly RevocationService $revocation,
        private readonly TokenIntrospectionService $introspection,
    ) {
    }

    public function authenticate(Request $request): TokenPayloadDTO
    {
        $token = $this->extractBearerToken($request);

        try {
            $payload = $this->validator->validate($token);
        } catch (RuntimeException $e) {
            if (! config('jwt.revocation.introspection_fallback', false)) {
                throw new InvalidTokenException('Unable to verify the access token at this time.', $e);
            }
            $payload = $this->introspection->introspect($token);
        }

        if ($this->revocation->isRevoked($payload->jwtId)) {
            throw new TokenRevokedException();
        }

        if ($this->revocation->isRevokedForUser($payload->subject, $payload->issuedAt)) {
            throw new TokenRevokedException('All sessions for this user have been revoked.');
        }

        return $payload;
    }

    private function extractBearerToken(Request $request): string
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            throw new InvalidTokenException('Missing bearer access token.');
        }

        $token = trim(substr($header, 7));

        if ($token === '') {
            throw new InvalidTokenException('Missing bearer access token.');
        }

        return $token;
    }
}
