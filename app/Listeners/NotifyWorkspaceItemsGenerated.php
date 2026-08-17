<?php

namespace App\Listeners;

use App\Contracts\WorkspaceNotifierInterface;
use App\Events\ItemsGenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotifyWorkspaceItemsGenerated implements ShouldQueue
{
    public int $tries = 5;

    public int $timeout = 30;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60, 300, 900];

    public function __construct(private readonly WorkspaceNotifierInterface $notifier) {}

    public function handle(ItemsGenerated $event): void
    {
        $this->notifier->notifyItemsGenerated($event->toPayload());
    }

    public function failed(ItemsGenerated $event, Throwable $exception): void
    {
        Log::error('Failed to notify Workspace Service of item generation after retries exhausted.', [
            'event_id' => $event->eventId,
            'entity_type' => $event->entityType,
            'entity_id' => $event->entity->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
