<?php

namespace App\Jobs;

use App\DTOs\TokenPayloadDTO;
use App\Services\User\UserSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncUserFromToken implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(private readonly array $claims) {}

    public function uniqueId(): string
    {
        return ($this->claims['sub'] ?? 'unknown') . ':' . ($this->claims['jti'] ?? 'unknown');
    }

    public function handle(UserSyncService $userSync): void
    {
        $userSync->syncFromToken(TokenPayloadDTO::fromClaims($this->claims));
    }
}
