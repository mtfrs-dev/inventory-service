<?php

namespace App\Actions\Item;

use App\Exceptions\Api\BusinessRuleException;
use App\Models\Category;
use App\Models\Item;
use App\Models\Subcategory;

class ValidateItemGenerationCapacityAction
{
    /**
     * @param  array<int, array{id: string, items_count: int}>  $subcategoryData
     */
    public function forCategory(Category $category, int $itemsCount, array $subcategoryData = []): void
    {
        $category->loadMissing('subcategories');

        if ($category->subcategories->isNotEmpty()) {
            $this->assertSubcategoryDataProvided($category, $subcategoryData);

            foreach ($subcategoryData as $data) {
                $this->forSubcategory($this->resolveSubcategory($category, $data['id']), $data['items_count']);
            }

            return;
        }

        $existing = Item::where('category_id', $category->id)->whereNull('subcategory_id')->count();

        $this->assertCapacity($category->expected_items_count, $existing, $itemsCount, "Kategori '{$category->name}'");
    }

    public function forSubcategory(Subcategory $subcategory, int $itemsCount): void
    {
        $existing = Item::where('subcategory_id', $subcategory->id)->count();

        $this->assertCapacity($subcategory->expected_items_count, $existing, $itemsCount, "Subkategori '{$subcategory->name}'");
    }

    private function assertSubcategoryDataProvided(Category $category, array $subcategoryData): void
    {
        if (empty($subcategoryData)) {
            throw new BusinessRuleException(
                "Kategori '{$category->name}' memiliki subkategori. Sertakan subcategories beserta items_count per subkategori."
            );
        }
    }

    private function resolveSubcategory(Category $category, string $subcategoryId): Subcategory
    {
        $subcategory = $category->subcategories->firstWhere('id', $subcategoryId);

        if (! $subcategory) {
            throw new BusinessRuleException(
                "Subkategori {$subcategoryId} bukan bagian dari kategori '{$category->name}'."
            );
        }

        return $subcategory;
    }

    private function assertCapacity(?int $expected, int $existing, int $requested, string $label): void
    {
        if ($expected === null) {
            throw new BusinessRuleException("{$label} belum memiliki jumlah item yang diharapkan (expected_items_count).");
        }

        $remaining = $expected - $existing;

        if ($requested > $remaining) {
            throw new BusinessRuleException("{$label} melebihi kapasitas. Sisa kapasitas: {$remaining} item.");
        }
    }
}
