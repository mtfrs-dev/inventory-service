<?php

namespace App\Actions\Item;

use App\Models\Item;
use App\Models\ItemStatus;
use App\Models\Project;
use App\Models\Status;
use App\Models\Subcategory;

abstract class GenerateItemsAction
{
    public function __construct(
        protected readonly GenerateItemQrCodeAction $qrAction
    ) {}

    protected function resolveStatus(): Status
    {
        return Status::where('code', 'RECEIVED')->firstOrFail();
    }

    protected function processSingleCategory(Project $project, \App\Models\Category $category, Status $status, int $itemsCount): void
    {
        $prefix     = "{$project->code}-{$project->year}_{$this->initials($category->name)}";
        $startIndex = $this->resolveStartIndex($prefix);

        for ($i = $startIndex + 1; $i <= $startIndex + $itemsCount; $i++) {
            $this->createItem($project->id, $category->id, null, $this->buildCode($prefix, $i), $status->id);
        }
    }

    protected function processSubcategory(
        string $projectId,
        string $categoryId,
        Subcategory $subcategory,
        string $categoryPrefix,
        string $statusId,
        int $itemsCount,
    ): void {
        $prefix     = "{$categoryPrefix}-{$this->initials($subcategory->name)}";
        $startIndex = $this->resolveStartIndex($prefix);

        for ($i = $startIndex + 1; $i <= $startIndex + $itemsCount; $i++) {
            $this->createItem($projectId, $categoryId, $subcategory->id, $this->buildCode($prefix, $i), $statusId);
        }
    }

    protected function createItem(string $projectId, string $categoryId, ?string $subcategoryId, string $code, string $statusId): void
    {
        $item = Item::withoutEvents(fn () => Item::create([
            'project_id'        => $projectId,
            'category_id'       => $categoryId,
            'subcategory_id'    => $subcategoryId,
            'code'              => $code,
            'current_status_id' => $statusId,
        ]));

        ItemStatus::create([
            'item_id'      => $item->id,
            'status_id'    => $statusId,
            'is_active'    => true,
            'effective_at' => now(),
        ]);

        $this->qrAction->handle($item);
    }

    protected function resolveStartIndex(string $prefix): int
    {
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $prefix);

        $last = Item::withTrashed()
            ->where('code', 'like', $escaped . '\\_' . '%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(code, "_", -1) AS UNSIGNED) DESC')
            ->value('code');

        if ($last === null) {
            return 0;
        }

        return (int) substr($last, strrpos($last, '_') + 1);
    }

    protected function buildCode(string $prefix, int $index): string
    {
        return $prefix . '_' . str_pad($index, 3, '0', STR_PAD_LEFT);
    }

    protected function initials(string $name): string
    {
        return strtoupper(implode('', array_map(
            fn ($word) => $word[0],
            array_filter(explode(' ', trim($name)))
        )));
    }
}
