<?php

namespace App\Jobs;

use App\Services\Jwt\JwksService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Proactively refreshes the cached JWKS so key rotation by the Auth
 * Service is invisible to request latency. Scheduled hourly.
 */
class RefreshJwksCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(JwksService $jwks): void
    {
        $jwks->forceRefresh();
    }
}
