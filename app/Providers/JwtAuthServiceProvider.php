<?php

namespace App\Providers;

use App\Auth\JwtGuard;
use App\Auth\JwtUserProvider;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class JwtAuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::provider('jwt', function () {
            return new JwtUserProvider();
        });

        Auth::extend('jwt', function ($app) {
            return new JwtGuard(
                $app->make(AuthenticationService::class),
                $app->make(Request::class)
            );
        });
    }
}
