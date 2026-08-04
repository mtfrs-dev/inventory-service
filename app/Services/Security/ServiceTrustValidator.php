<?php

namespace App\Services\Security;

use App\Exceptions\ServiceTrustException;
use Illuminate\Http\Request;

/**
 * Verifies inbound server-to-server requests (e.g. Auth Service webhooks)
 * via an HMAC signature over the request body and timestamp, independent
 * from end-user JWT authentication.
 */
class ServiceTrustValidator
{
    public function validate(Request $request, string $rawBody): void
    {
        if (! config('services_trust.enabled', true)) {
            return;
        }

        $serviceId = $request->header(config('services_trust.id_header'));
        $signature = $request->header(config('services_trust.header'));
        $timestamp = $request->header(config('services_trust.timestamp_header'));

        if (empty($serviceId) || empty($signature) || empty($timestamp)) {
            throw new ServiceTrustException('Missing service trust headers.');
        }

        $secret = config("services_trust.trusted_services.{$serviceId}.secret");

        if (empty($secret)) {
            throw new ServiceTrustException('Unknown calling service.');
        }

        $maxSkew = (int) config('services_trust.max_clock_skew', 60);

        if (abs(time() - (int) $timestamp) > $maxSkew) {
            throw new ServiceTrustException('Service request timestamp is outside the allowed window.');
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $secret);

        if (! hash_equals($expected, $signature)) {
            throw new ServiceTrustException('Service request signature is invalid.');
        }
    }
}
