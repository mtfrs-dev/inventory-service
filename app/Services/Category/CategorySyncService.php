<?php

namespace App\Services\Category;

use App\Contracts\ExternalProjectServiceInterface;
use App\Exceptions\Api\ResourceNotFoundException;
use App\Models\Category;
use App\Models\Item;
use App\Models\Subcategory;

class CategorySyncService
{
    public function __construct(
        private readonly ExternalProjectServiceInterface $externalService,
    ) {}

    public function syncCategory(Category $category): Category
    {
        if (! $category->external_project_id || ! $category->external_id) {
            return $category;
        }

        $externalCategories = $this->externalService->fetchCategories(
            (string) $category->external_project_id
        );

        $externalData = collect($externalCategories)->firstWhere('id', $category->external_id);

        if (! $externalData) {
            throw new ResourceNotFoundException('Kategori tidak ditemukan di layanan PM.');
        }

        $category->update([
            'name' => $externalData['name'],
            'expected_items_count' => $externalData['qty_total'],
        ]);

        return $category->fresh();
    }

    public function syncSubcategoriesForCategory(Category $category): void
    {
        if (! $category->external_id) {
            return;
        }

        $externalSubcategories = $this->externalService->fetchSubcategories(
            (string) $category->external_id
        );

        $externalIds = array_column($externalSubcategories, 'id');

        foreach ($externalSubcategories as $item) {
            $this->upsertSubcategory($category, $item);
        }

        $this->softDeleteRemovedSubcategories($category, $externalIds);
    }

    public function syncSubcategory(Subcategory $subcategory): Subcategory
    {
        $subcategory->loadMissing('category');
        $category = $subcategory->category;

        if (! $category->external_id || ! $subcategory->external_id) {
            return $subcategory;
        }

        $externalSubcategories = $this->externalService->fetchSubcategories(
            (string) $category->external_id
        );

        $externalData = collect($externalSubcategories)->firstWhere('id', $subcategory->external_id);

        if (! $externalData) {
            throw new ResourceNotFoundException('Subkategori tidak ditemukan di layanan PM.');
        }

        $subcategory->update([
            'name' => $externalData['name'],
            'expected_items_count' => $externalData['qty_total'],
        ]);

        return $subcategory->fresh();
    }

    private function upsertSubcategory(Category $category, array $item): Subcategory
    {
        $subcategory = Subcategory::withTrashed()
            ->where('category_id', $category->id)
            ->where('external_id', $item['id'])
            ->first();

        if ($subcategory) {
            if ($subcategory->trashed()) {
                Item::withTrashed()->where('subcategory_id', $subcategory->id)->restore();
                $subcategory->restore();
            }

            $subcategory->update([
                'external_category_id' => $category->external_id,
                'code' => $this->buildSubcategoryCode($item['name'], $item['id']),
                'name' => $item['name'],
                'description' => $item['description'] ?? '',
                'expected_items_count' => $item['qty_total'],
            ]);
        } else {
            $subcategory = Subcategory::create([
                'category_id' => $category->id,
                'external_id' => $item['id'],
                'external_category_id' => $category->external_id,
                'code' => $this->buildSubcategoryCode($item['name'], $item['id']),
                'name' => $item['name'],
                'description' => $item['description'] ?? '',
                'expected_items_count' => $item['qty_total'],
            ]);
        }

        return $subcategory->fresh();
    }

    private function softDeleteRemovedSubcategories(Category $category, array $externalIds): void
    {
        Subcategory::where('category_id', $category->id)
            ->whereNotNull('external_id')
            ->whereNotIn('external_id', $externalIds)
            ->get()
            ->each(function (Subcategory $subcategory) {
                Item::where('subcategory_id', $subcategory->id)->delete();
                $subcategory->delete();
            });
    }

    private function buildSubcategoryCode(string $name, int $externalId): string
    {
        $initials = collect(explode(' ', $name))
            ->map(fn (string $word) => strtoupper($word[0]))
            ->implode('');

        return $initials.'-'.$externalId;
    }
}
