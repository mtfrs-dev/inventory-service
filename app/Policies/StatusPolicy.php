<?php

namespace App\Policies;

use App\Auth\AuthenticatedUser;
use App\Models\StatusLog;

class StatusPolicy
{
    public function view(AuthenticatedUser $user, ?StatusLog $statusLog = null): bool
    {
        return $user->hasPermission('status.view');
    }

    public function update(AuthenticatedUser $user, StatusLog $statusLog): bool
    {
        return $user->hasPermission('status.update');
    }
}
