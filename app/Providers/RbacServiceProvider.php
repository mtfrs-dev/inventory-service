<?php

namespace App\Providers;

use App\Auth\AuthenticatedUser;
use App\Policies\AuditPolicy;
use App\Policies\ItemPolicy;
use App\Policies\ReportPolicy;
use App\Policies\StatusPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('item.view',       [ItemPolicy::class,     'view']);
        Gate::define('item.create',     [ItemPolicy::class,     'create']);
        Gate::define('item.update',     [ItemPolicy::class,     'update']);
        Gate::define('item.delete',     [ItemPolicy::class,     'delete']);

        Gate::define('status.view',     [StatusPolicy::class,   'view']);
        Gate::define('status.update',   [StatusPolicy::class,   'update']);

        Gate::define('report.generate', [ReportPolicy::class,   'generate']);
        Gate::define('report.view',     [ReportPolicy::class,   'view']);

        Gate::define('audit.view',      [AuditPolicy::class,    'view']);

        Gate::before(function (?AuthenticatedUser $user, string $ability) {
            return $user?->hasRole('super_admin') ? true : null;
        });
    }
}
