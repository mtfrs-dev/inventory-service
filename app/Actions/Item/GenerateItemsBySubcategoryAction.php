<?php

namespace App\Actions\Item;

use App\Models\Subcategory;

class GenerateItemsBySubcategoryAction extends GenerateItemsAction
{
    public function __construct(
        GenerateItemQrCodeAction $qrAction,
        private readonly ValidateItemGenerationCapacityAction $capacityValidator,
    ) {
        parent::__construct($qrAction);
    }

    public function handle(Subcategory $subcategory, int $itemsCount): void
    {
        $status = $this->resolveStatus();
        $subcategory->loadMissing(['category.project']);

        $this->capacityValidator->forSubcategory($subcategory, $itemsCount);

        $category = $subcategory->category;
        $prefix   = "{$category->project->code}-{$category->project->year}_{$this->initials($category->name)}";

        $this->processSubcategory(
            $category->project->id,
            $category->id,
            $subcategory,
            $prefix,
            $status->id,
            $itemsCount,
        );
    }
}
