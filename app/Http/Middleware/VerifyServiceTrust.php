<?php

namespace App\Http\Middleware;

use App\Exceptions\ServiceTrustException;
use App\Services\Security\ServiceTrustValidator;
use Closure;
use Illuminate\Http\Request;

/**
 * Applied to inbound Auth Service webhook routes only — independent
 * from end-user JWT authentication.
 */
class VerifyServiceTrust
{
    public function __construct(private readonly ServiceTrustValidator $validator)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            $this->validator->validate($request, $request->getContent());
        } catch (ServiceTrustException $e) {
            return response()->json([
                'error' => 'forbidden',
                'message' => $e->getMessage(),
            ], 403);
        }

        return $next($request);
    }
}
