<?php

namespace App\Actions\Item;

use App\Contracts\ExternalProjectServiceInterface;
use App\Models\Category;
use App\Models\Project;
use App\Models\Subcategory;
use App\Services\Category\CategorySyncService;
use App\Services\Project\ProjectSyncService;

class EnsureEntitySyncedAction
{
    public function __construct(
        private readonly ExternalProjectServiceInterface $externalService,
        private readonly ProjectSyncService $projectSync,
        private readonly CategorySyncService $categorySync,
    ) {}

    public function handle(
        string $entityType,
        ?string $parentId,
        ?int $externalParentId,
        ?string $entityId,
        ?int $externalEntityId,
    ): Category|Subcategory {
        if ($entityType === 'category') {
            $project = $this->syncProject($parentId, $externalParentId);

            return $this->resolveCategory($project, $entityId, $externalEntityId);
        }

        $category = $this->syncCategoryParent($parentId, $externalParentId); 

        return $this->resolveSubcategory($category, $entityId, $externalEntityId);
    }

    private function syncProject(?string $parentId, ?int $externalParentId): Project
    {
        if ($externalParentId !== null) {
            return $this->syncProjectByExternalId($externalParentId);
        }

        $project = Project::findOrFail($parentId);

        if ($project->external_id) {
            return $this->syncProjectByExternalId((int) $project->external_id);
        }

        return $project;
    }

    private function syncProjectByExternalId(int $externalProjectId): Project 
    {
        $data = $this->externalService->fetchProject((string) $externalProjectId);

        return $this->projectSync->upsert($data);
    }

    /**
     * Resolves the parent Category for a subcategory generation request.
     * A category located only by external_parent_id but never synced locally is
     * bootstrapped by syncing its parent Project, which cascades the full
     * category/subcategory tree (mirrors syncProject()'s cascading behavior).
     */
    private function syncCategoryParent(?string $parentId, ?int $externalParentId): Category 
    {
        if ($externalParentId !== null) {
            $category = Category::where('external_id', $externalParentId)->first();

            if ($category) {
                return $this->refreshCategory($category);
            }

            $categoryData = $this->externalService->fetchCategory((string) $externalParentId);
            $this->syncProjectByExternalId((int) $categoryData['project_id']);

            return Category::where('external_id', $externalParentId)->firstOrFail();
        }

        return $this->refreshCategory(Category::findOrFail($parentId));
    }

    private function refreshCategory(Category $category): Category
    {
        if (! $category->external_id) {
            return $category;
        }

        $category = $this->categorySync->syncCategory($category);
        $this->categorySync->syncSubcategoriesForCategory($category);

        return $category;
    }

    private function resolveCategory(Project $project, ?string $entityId, ?int $externalEntityId): Category
    {
        if ($entityId !== null) {
            return Category::where('id', $entityId)
                ->where('project_id', $project->id)
                ->firstOrFail();
        }

        return Category::where('external_id', $externalEntityId)
            ->where('project_id', $project->id)
            ->firstOrFail();
    }

    private function resolveSubcategory(Category $category, ?string $entityId, ?int $externalEntityId): Subcategory
    {
        if ($entityId !== null) {
            return Subcategory::where('id', $entityId)
                ->where('category_id', $category->id)
                ->firstOrFail();
        }

        return Subcategory::where('external_id', $externalEntityId)
            ->where('category_id', $category->id)
            ->firstOrFail();
    }
}
