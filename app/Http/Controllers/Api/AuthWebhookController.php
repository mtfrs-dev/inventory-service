<?php

namespace App\Http\Controllers\Api;

use App\Events\TokenRevoked;
use App\Http\Controllers\Controller;
use App\Services\Auth\RevocationService;
use App\Services\User\UserSyncService;
use Illuminate\Http\Request;

/**
 * Inbound webhooks from the Auth Service. Protected by the
 * "service.trust" middleware (HMAC), not "jwt.auth" — these are
 * server-to-server calls, not end-user requests.
 */
class AuthWebhookController extends Controller
{
    public function __construct(
        private readonly RevocationService $revocation,
        private readonly UserSyncService $userSync,
    ) {
    }

    public function tokenRevoked(Request $request)
    {
        $validated = $request->validate([
            'jti' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'ttl' => ['required', 'integer', 'min:1'],
        ]);

        if (! empty($validated['jti'])) {
            $this->revocation->revoke($validated['jti'], $validated['ttl']);
        }

        if (! empty($validated['subject'])) {
            $this->revocation->revokeAllForUser($validated['subject'], $validated['ttl']);
        }

        event(new TokenRevoked($validated['jti'] ?? null, $validated['subject'] ?? null, now()->timestamp));

        return response()->json(['status' => 'acknowledged']);
    }

    public function userDeactivated(Request $request)
    {
        $validated = $request->validate([
            'external_id' => ['required', 'string'],
        ]);

        $this->userSync->deactivate($validated['external_id']);

        return response()->json(['status' => 'acknowledged']);
    }
}
