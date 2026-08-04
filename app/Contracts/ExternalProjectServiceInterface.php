<?php

namespace App\Contracts;

interface ExternalProjectServiceInterface
{
    public function fetchProject(string $externalId): array;

    public function fetchCategories(string $externalProjectId): array;

    public function fetchCategory(string $externalCategoryId): array;

    public function fetchSubcategories(string $externalCategoryId): array;
}
