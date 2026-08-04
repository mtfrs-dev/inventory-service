<?php

namespace App\Http\Middleware;

use App\Exceptions\InsufficientPermissionException;
use App\Services\Rbac\PermissionService;
use Closure;
use Illuminate\Http\Request;

class EnsurePermission
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function handle(Request $request, Closure $next, string ...$requiredPermissions)
    {
        $payload = $request->attributes->get('jwt_payload');

        if ($payload === null) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Missing authenticated token context.',
            ], 401);
        }

        foreach ($requiredPermissions as $permission) {
            try {
                $this->permissions->authorizePermission($payload, $permission);
            } catch (InsufficientPermissionException $e) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => $e->getMessage(),
                ], 403);
            }
        }

        return $next($request);
    }
}
