<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Service Trust
    |--------------------------------------------------------------------------
    | Validates inbound server-to-server calls (e.g. Auth Service webhooks)
    | via HMAC-signed headers. Independent from end-user JWT authentication.
    */
    'enabled' => (bool) env('SERVICE_TRUST_ENABLED', true),

    'header' => 'X-Service-Signature',
    'id_header' => 'X-Service-Id',
    'timestamp_header' => 'X-Service-Timestamp',

    'max_clock_skew' => (int) env('SERVICE_TRUST_MAX_SKEW', 60),

    'trusted_services' => [
        'auth-service' => [
            'secret' => env('SERVICE_TRUST_AUTH_SERVICE_SECRET'),
        ],
        'gateway' => [
            'secret' => env('SERVICE_TRUST_GATEWAY_SECRET'),
        ],
    ],

];
