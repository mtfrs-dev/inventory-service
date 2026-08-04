<?php

namespace App\Repositories\Contracts;

use App\Models\Subcategory;
use Illuminate\Pagination\LengthAwarePaginator;

interface SubcategoryRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): Subcategory;

    public function create(array $data): Subcategory;

    public function update(string $id, array $data): Subcategory;

    public function delete(string $id): bool;
}
