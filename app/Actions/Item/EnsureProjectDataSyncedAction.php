<?php

namespace App\Actions\Item;

use App\Contracts\ExternalProjectServiceInterface;
use App\Exceptions\CategoryItemCountEmptyException;
use App\Exceptions\SpectechMissingException;
use App\Models\Project;
use App\Services\Project\ProjectSyncService;

class EnsureProjectDataSyncedAction
{
    public function __construct(
        private readonly ExternalProjectServiceInterface $externalService,
        private readonly ProjectSyncService $projectSync,
    ) {}

    public function handle(?string $projectId, ?int $externalProjectId): Project
    {
        $project = $this->resolveProject($projectId, $externalProjectId);
        $this->validateCategories($project);

        return $project->fresh(['categories.subcategories']);
    }

    private function resolveProject(?string $projectId, ?int $externalProjectId): Project
    {
        if ($projectId !== null) {
            return Project::findOrFail($projectId);
        }

        return $this->projectSync->upsert(
            $this->externalService->fetchProject((string) $externalProjectId)
        );
    }

    private function validateCategories(Project $project): void
    {
        $categories = $project->categories()->get();

        if ($categories->isEmpty()) {
            throw new SpectechMissingException($project->code, $project->year);
        }
        
        $emptyQty = $categories->contains(function ($category) {
            return empty($category->expected_items_count);
        });

        if ($emptyQty) {
            throw new CategoryItemCountEmptyException($project->code, $project->year);
        }
    }
}
