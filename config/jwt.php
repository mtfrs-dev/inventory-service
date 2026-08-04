<?php

return [

    'issuer'            => env('JWT_ISSUER', 'https://auth.example.com'),
    'audience'          => env('JWT_AUDIENCE', 'inventory-service'),
    'algo'              => env('JWT_ALGO', 'RS256'),
    'leeway'            => (int) env('JWT_LEEWAY_SECONDS', 5),
    'required_claims'   => [
        'sub',
        'iss',
        'aud',
        'exp',
        'iat',
        'jti',
        'roles',
        'permissions'
    ],

    /*
    |--------------------------------------------------------------------------
    | JWKS (key rotation support)
    |--------------------------------------------------------------------------
    */
    'jwks' => [
        'uri' => env('AUTH_SERVICE_JWKS_URI', 'https://auth.example.com/.well-known/jwks.json'),
        'cache_key' => 'auth_service.jwks',
        'cache_ttl' => (int) env('JWKS_CACHE_TTL', 21600),
        'http_timeout' => (int) env('JWKS_HTTP_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Revocation
    |--------------------------------------------------------------------------
    | Revocation state is pushed from the Auth Service via signed webhooks
    | and stored in a denylist. Introspection is an optional synchronous
    | fallback used only when the JWKS endpoint is unreachable.
    */
    'revocation' => [
        'enabled' => (bool) env('JWT_REVOCATION_ENABLED', true),
        'store' => env('JWT_REVOCATION_STORE', 'redis'),
        'denylist_prefix' => 'jwt:revoked:',
        'introspection_fallback' => (bool) env('JWT_INTROSPECTION_FALLBACK', false),
        'introspection_uri' => env('AUTH_SERVICE_INTROSPECTION_URI', 'https://auth.example.com/oauth/introspect'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Refresh
    |--------------------------------------------------------------------------
    | Inventory Service is a pure resource server. Refresh tokens are never
    | issued, stored, or redeemed here — clients refresh directly against
    | the Auth Service token endpoint. This entry exists only as a
    | documented reference for integrators.
    */
    'refresh' => [
        'handled_by' => 'auth-service',
        'auth_service_token_endpoint' => env('AUTH_SERVICE_TOKEN_ENDPOINT', 'https://auth.example.com/oauth/token'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSO
    |--------------------------------------------------------------------------
    | A single access token is valid across every resource server that
    | shares this issuer and is listed as a trusted audience.
    */
    'sso' => [
        'shared_issuer' => true,
        'trusted_audiences' => array_filter(explode(',', env('JWT_TRUSTED_AUDIENCES', 'inventory-service'))),
    ],

];
