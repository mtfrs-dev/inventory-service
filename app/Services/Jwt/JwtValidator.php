<?php

namespace App\Services\Jwt;

use App\DTOs\TokenPayloadDTO;
use App\Exceptions\InvalidTokenException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

class JwtValidator
{
    public function __construct(private readonly JwksService $jwks) {}

    public function validate(string $token): TokenPayloadDTO
    {
        JWT::$leeway = (int) config('jwt.leeway', 5);

        $keyId  = $this->extractKeyId($token);
        $jwk    = $this->jwks->getKeyForKeyId($keyId);

        try {
            $key        = JWK::parseKey($jwk, config('jwt.algo', 'RS256'));
            $decoded    = JWT::decode($token, $key);
        } catch (ExpiredException $e) {
            throw new InvalidTokenException('Access Token kadaluarsa.', $e);
        } catch (SignatureInvalidException $e) {
            throw new InvalidTokenException('Access Token Signature Invalid.', $e);
        } catch (BeforeValidException $e) {
            throw new InvalidTokenException('Access Token is not yet valid.', $e);
        } catch (UnexpectedValueException | \DomainException $e) {
            throw new InvalidTokenException('Tidak dapat mendecode Access Token.', $e);
        }

        $claims = json_decode(json_encode($decoded), true);

        $this->assertRequiredClaims($claims);
        $this->assertIssuer($claims);
        $this->assertAudience($claims);

        return TokenPayloadDTO::fromClaims($claims, $keyId);
    }

    private function extractKeyId(string $token): string
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new InvalidTokenException('Access Token is malformed.');
        }

        $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true);

        if (! is_array($header)) {
            throw new InvalidTokenException('Access Token header is malformed.');
        }

        if (($header['alg'] ?? null) !== config('jwt.algo', 'RS256')) {
            throw new InvalidTokenException('Access Token uses an unsupported algorithm.');
        }

        if (empty($header['kid'])) {
            throw new InvalidTokenException('Access Token is missing a key identifier.');
        }

        return $header['kid'];
    }

    private function assertRequiredClaims(array $claims): void
    {
        foreach (config('jwt.required_claims', []) as $claim) {
            if (! array_key_exists($claim, $claims)) {
                throw new InvalidTokenException("Access Token is missing the required claim [{$claim}].");
            }
        }
    }

    private function assertIssuer(array $claims): void
    {
        if (($claims['iss'] ?? null) !== config('jwt.issuer')) {
            throw new InvalidTokenException('Access Token issuer is not trusted.');
        }
    }

    private function assertAudience(array $claims): void
    {
        $audience = $claims['aud'] ?? [];
        if (is_string($audience)) {
            $audience = [$audience];
        }

        $trusted = config('jwt.sso.trusted_audiences', []);

        if (count(array_intersect($audience, $trusted)) === 0) {
            throw new InvalidTokenException('Access Token audience is not trusted by this service.');
        }
    }
}
