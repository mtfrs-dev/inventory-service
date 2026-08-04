<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'super_admin' => 'Super Admin',
        'inventory_manager' => 'Inventory Manager',
        'inventory_operator' => 'Inventory Operator',
        'viewer' => 'Viewer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'item.view',
        'item.create',
        'item.update',
        'item.delete',
        'status.view',
        'status.update',
        'report.generate',
        'report.view',
        'audit.view',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Ceilings (defense in depth)
    |--------------------------------------------------------------------------
    | Independent, locally-owned upper bound on what each role may ever
    | claim, regardless of what the token asserts. Protects against claim
    | tampering or drift between the Auth Service and this service's
    | expectation of a role's authority. A token claiming a permission
    | outside its role's ceiling is rejected even if the permission claim
    | itself is present.
    */
    'role_ceiling' => [
        'super_admin' => [
            'item.view', 'item.create', 'item.update', 'item.delete',
            'status.view', 'status.update',
            'report.generate', 'report.view',
            'audit.view',
        ],
        'inventory_manager' => [
            'item.view', 'item.create', 'item.update', 'item.delete',
            'status.view', 'status.update',
            'report.generate', 'report.view',
        ],
        'inventory_operator' => [
            'item.view', 'item.update',
            'status.view', 'status.update',
        ],
        'viewer' => [
            'item.view',
            'status.view',
            'report.view',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    | OAuth2 client-grant scopes, distinct from user roles. A request must
    | satisfy both its required scope and its required permission/role.
    */
    'scopes' => [
        'inventory:read',
        'inventory:write',
        'inventory:reports',
    ],

];
