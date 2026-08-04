<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): Category;

    public function create(array $data): Category;

    public function update(string $id, array $data): Category;

    public function delete(string $id): bool;
}
