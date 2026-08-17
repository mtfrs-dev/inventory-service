<?php

namespace App\Providers;

use App\Contracts\ExternalProjectServiceInterface;
use App\Contracts\WorkspaceNotifierInterface;
use App\Services\ExternalProject\HttpExternalProjectService;
use App\Services\Workspace\HttpWorkspaceNotifier;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExternalProjectServiceInterface::class, HttpExternalProjectService::class);
        $this->app->bind(WorkspaceNotifierInterface::class, HttpWorkspaceNotifier::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Rate Limiting
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $key = $request->attributes->get('jwt_payload')?->subject ?? $request->ip();

            return Limit::perMinute(120)->by($key);
        });

        RateLimiter::for('reports', function (Request $request) {
            $key = $request->attributes->get('jwt_payload')?->subject ?? $request->ip();

            return Limit::perMinute(10)->by('reports:'.$key);
        });

        RateLimiter::for('service-webhooks', function (Request $request) {
            $key = $request->header('X-Service-Id') ?? $request->ip();

            return Limit::perMinute(60)->by('service-webhooks:'.$key);
        });
    }
}
