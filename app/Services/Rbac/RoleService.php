<?php

namespace App\Services\Rbac;

/**
 * Read-only helpers over the rbac config map. Centralizes role/permission
 * metadata so it is never hard-coded inline elsewhere.
 */
class RoleService
{
    public function isKnownRole(string $role): bool
    {
        return array_key_exists($role, config('rbac.roles', []));
    }

    public function displayName(string $role): string
    {
        return config("rbac.roles.{$role}", $role);
    }

    public function allPermissionsForRole(string $role): array
    {
        return config("rbac.role_ceiling.{$role}", []);
    }
}
