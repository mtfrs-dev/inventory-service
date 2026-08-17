<?php

namespace App\Events;

use App\Models\Category;
use App\Models\Project;
use App\Models\Subcategory;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ItemsGenerated
{
    use Dispatchable, SerializesModels;

    public readonly string $eventId;

    public readonly CarbonInterface $occurredAt;

    public function __construct(
        public readonly string $entityType,
        public readonly Category|Subcategory $entity,
        public readonly Project $project,
        public readonly int $itemsCount,
    ) {
        $this->eventId = (string) Str::uuid();
        $this->occurredAt = now();
    }

    public function toPayload(): array
    {
        return [
            'event' => 'inventory.items.generated',
            'event_id' => $this->eventId,
            'occurred_at' => $this->occurredAt->toIso8601String(),
            'data' => [
                'entity_type' => $this->entityType,
                'entity_id' => $this->entity->id,
                'external_entity_id' => $this->entity->external_id,
                'project_id' => $this->project->id,
                'external_project_id' => $this->project->external_id,
                'items_count' => $this->itemsCount,
            ],
        ];
    }
}
