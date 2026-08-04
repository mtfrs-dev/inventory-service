<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Fail-closed by design: a missing scope claim causes every scope
 * check to fail rather than defaulting to allow.
 */
class EnsureScope
{
    public function handle(Request $request, Closure $next, string ...$scopes)
    {
        $payload = $request->attributes->get('jwt_payload');

        if ($payload === null) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Missing authenticated token context.',
            ], 401);
        }

        foreach ($scopes as $scope) {
            if (! $payload->hasScope($scope)) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => "Missing required scope [{$scope}].",
                ], 403);
            }
        }

        return $next($request);
    }
}
