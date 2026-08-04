<?php

namespace App\Policies;

use App\Auth\AuthenticatedUser;

class ReportPolicy
{
    public function generate(AuthenticatedUser $user): bool
    {
        return $user->hasPermission('report.generate');
    }

    public function view(AuthenticatedUser $user): bool
    {
        return $user->hasPermission('report.view');
    }
}
