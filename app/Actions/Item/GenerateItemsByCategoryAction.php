<?php

namespace App\Actions\Item;

use App\Models\Category;

class GenerateItemsByCategoryAction extends GenerateItemsAction
{
    public function __construct(
        GenerateItemQrCodeAction $qrAction,
        private readonly ValidateItemGenerationCapacityAction $capacityValidator,
    ) {
        parent::__construct($qrAction);
    }

    public function handle(Category $category, int $itemsCount, array $subcategoryData = []): void
    {
        $status = $this->resolveStatus();
        $category->loadMissing(['project', 'subcategories']);

        $this->capacityValidator->forCategory($category, $itemsCount, $subcategoryData);

        $prefix = "{$category->project->code}-{$category->project->year}_{$this->initials($category->name)}";

        if ($category->subcategories->isNotEmpty()) {
            foreach ($subcategoryData as $data) {
                $subcategory = $category->subcategories->firstWhere('id', $data['id']);
                $this->processSubcategory(
                    $category->project->id,
                    $category->id,
                    $subcategory,
                    $prefix,
                    $status->id,
                    $data['items_count'],
                );
            }
        } else {
            $this->processSingleCategory($category->project, $category, $status, $itemsCount);
        }
    }
}
