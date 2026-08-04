<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $payload = $request->attributes->get('jwt_payload');

        if ($payload === null || ! $payload->hasAnyRole($roles)) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'Your role does not permit this action.',
            ], 403);
        }

        return $next($request);
    }
}
