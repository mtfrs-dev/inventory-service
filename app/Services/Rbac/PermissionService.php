<?php

namespace App\Services\Rbac;

use App\DTOs\TokenPayloadDTO;
use App\Exceptions\InsufficientPermissionException;

/**
 * Authorization checks enforced against the verified token, with an
 * independent role-ceiling check as defense-in-depth against claim
 * tampering or drift between the Auth Service's notion of a role and
 * this service's expectation of it.
 */
class PermissionService
{
    public function authorizePermission(TokenPayloadDTO $token, string $permission): void
    {
        $this->assertWithinCeiling($token, $permission);

        if (! $token->hasPermission($permission)) {
            throw new InsufficientPermissionException("Missing required permission [{$permission}].");
        }
    }

    public function authorizeRole(TokenPayloadDTO $token, array $allowedRoles): void
    {
        if (! $token->hasAnyRole($allowedRoles)) {
            throw new InsufficientPermissionException('Your role does not permit this action.');
        }
    }

    public function authorizeScope(TokenPayloadDTO $token, string $scope): void
    {
        if (! $token->hasScope($scope)) {
            throw new InsufficientPermissionException("Missing required scope [{$scope}].");
        }
    }

    private function assertWithinCeiling(TokenPayloadDTO $token, string $permission): void
    {
        $ceilings = config('rbac.role_ceiling', []);

        foreach ($token->roles as $role) {
            $ceiling = $ceilings[$role] ?? [];

            if (in_array($permission, $ceiling, true)) {
                return;
            }
        }

        throw new InsufficientPermissionException(
            "Permission [{$permission}] exceeds the allowed ceiling for the token's roles."
        );
    }
}
