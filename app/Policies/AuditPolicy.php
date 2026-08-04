<?php

namespace App\Policies;

use App\Auth\AuthenticatedUser;

class AuditPolicy
{
    public function view(AuthenticatedUser $user): bool
    {
        return $user->hasPermission('audit.view');
    }
}
