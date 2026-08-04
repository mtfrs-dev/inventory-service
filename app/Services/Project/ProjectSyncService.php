<?php

namespace App\Services\Project;

use App\Events\ProjectDeleted;
use App\Events\ProjectSynced;
use App\Exceptions\Api\IncompleteProjectDataException;
use App\Models\Category;
use App\Models\Item;
use App\Models\Project;
use App\Models\Subcategory;
use App\Services\Category\CategorySyncService;

class ProjectSyncService
{
    private const REQUIRED_FIELDS = ['id', 'name', 'code', 'start_date', 'project_leader_id'];

    public function __construct(
        private readonly CategorySyncService $categorySync,
    ) {}

    public function upsert(array $data): Project // S.6.
    {
        $this->assertCompleteProjectData($data);

        $project = Project::updateOrCreate(
            ['external_id' => $data['id']],
            [
                'name' => $data['name'],
                'code' => $data['code'],
                'year' => explode('-', $data['start_date'])[0],
                'external_pic_id' => $data['project_leader_id'],
                'last_synced_at' => now(),
            ]
        );

        event(new ProjectSynced($project));

        if (! empty($data['specktech'])) {
            $this->syncCategoriesFromSpecktech($project, $data['specktech']);
        }

        return $project;
    }

    private function assertCompleteProjectData(array $data): void
    {
        $missing = array_values(array_filter(
            self::REQUIRED_FIELDS,
            fn (string $field) => ! isset($data[$field]) || $data[$field] === ''
        ));

        if (! empty($missing)) {
            throw new IncompleteProjectDataException($missing);
        }
    }

    public function delete(string $externalId): void
    {
        $project = Project::where('external_id', $externalId)->first();

        if ($project) {
            event(new ProjectDeleted($project));
            $project->delete();
        }
    }

    private function syncCategoriesFromSpecktech(Project $project, array $specktech): void
    {
        $externalIds = array_column($specktech, 'id');

        foreach ($specktech as $item) {
            $category = $this->upsertCategory($project, $item);
            $this->categorySync->syncSubcategoriesForCategory($category);
        }

        $this->softDeleteRemovedCategories($project, $externalIds);
    }

    private function upsertCategory(Project $project, array $item): Category
    {
        $code = $this->buildCategoryCode($item['name'], $item['id']);

        $category = Category::withTrashed()
            ->where('project_id', $project->id)
            ->where('code', $code)
            ->first();

        if ($category) {
            if ($category->trashed()) {
                $this->restoreCategoryWithChildren($category);
                $category->restore();
            }

            $category->update([
                'external_project_id' => $project->external_id,
                'external_id' => $item['id'],
                'name' => $item['name'],
                'expected_items_count' => $item['qty_total'],
            ]);
        } else {
            $category = Category::create([
                'project_id' => $project->id,
                'external_project_id' => $project->external_id,
                'external_id' => $item['id'],
                'code' => $code,
                'name' => $item['name'],
                'expected_items_count' => $item['qty_total'],
            ]);
        }

        return $category->fresh();
    }

    private function restoreCategoryWithChildren(Category $category): void
    {
        $category->subcategories()->withTrashed()->get()->each(function (Subcategory $subcategory) {
            Item::withTrashed()->where('subcategory_id', $subcategory->id)->restore();
            $subcategory->restore();
        });

        Item::withTrashed()
            ->where('category_id', $category->id)
            ->whereNull('subcategory_id')
            ->restore();
    }

    private function softDeleteRemovedCategories(Project $project, array $externalIds): void
    {
        Category::where('project_id', $project->id)
            ->whereNotNull('external_id')
            ->whereNotIn('external_id', $externalIds)
            ->get()
            ->each(function (Category $category) {
                $category->subcategories->each(function (Subcategory $subcategory) {
                    Item::where('subcategory_id', $subcategory->id)->delete();
                    $subcategory->delete();
                });

                Item::where('category_id', $category->id)
                    ->whereNull('subcategory_id')
                    ->delete();

                $category->delete();
            });
    }

    private function buildCategoryCode(string $name, int $externalId): string
    {
        $initials = collect(explode(' ', $name))
            ->map(fn (string $word) => strtoupper($word[0]))
            ->implode('');

        return $initials.'-'.$externalId;
    }
}
