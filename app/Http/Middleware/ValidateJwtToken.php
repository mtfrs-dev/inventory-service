<?php

namespace App\Http\Middleware;

use App\Exceptions\InvalidTokenException;
use App\Exceptions\TokenRevokedException;
use App\Jobs\SyncUserFromToken;
use App\Services\Auth\AuthenticationService;
use Closure;
use Illuminate\Http\Request;

class ValidateJwtToken
{
    public function __construct(private readonly AuthenticationService $authentication){}

    public function handle(Request $request, Closure $next)
    {
        try {
            $payload = $this->authentication->authenticate($request);
        } catch (InvalidTokenException|TokenRevokedException $e) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => $e->getMessage(),
            ], 401);
        }

        $request->attributes->set('jwt_payload', $payload);

        SyncUserFromToken::dispatch($payload->toArray());

        return $next($request);
    }
}
