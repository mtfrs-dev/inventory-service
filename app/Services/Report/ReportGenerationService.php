<?php

namespace App\Services\Report;

use App\Models\Item;
use App\Models\ItemStatus;
use Illuminate\Support\Carbon;

class ReportGenerationService
{
    public function generate(string $type, ?string $from = null, ?string $to = null): array
    {
        $data = match ($type) {
            'inventory_summary' => $this->inventorySummary(),
            'low_stock' => $this->lowStock(),
            'status_changes' => $this->statusChanges($from, $to),
            default => [],
        };

        return [
            'type' => $type,
            'generated_at' => now()->toIso8601String(),
            'period' => [
                'from' => $from,
                'to' => $to,
            ],
            'data' => $data,
        ];
    }

    public function recent(): array
    {
        // Reports are generated on demand and not persisted in this
        // service. Placeholder for a future persisted-report listing.
        return [];
    }

    private function inventorySummary(): array
    {
        return [
            'total_items' => Item::count(),
            'total_quantity' => (int) Item::sum('quantity'),
        ];
    }

    private function lowStock(): array
    {
        return Item::where('quantity', '<=', 10)
            ->orderBy('quantity')
            ->get(['id', 'sku', 'name', 'quantity'])
            ->toArray();
    }

    private function statusChanges(?string $from, ?string $to): array
    {
        $query = ItemStatus::query();

        if ($from) {
            $query->where('created_at', '>=', Carbon::parse($from));
        }

        if ($to) {
            $query->where('created_at', '<=', Carbon::parse($to));
        }

        return $query->orderByDesc('created_at')->limit(500)->get()->toArray();
    }
}
