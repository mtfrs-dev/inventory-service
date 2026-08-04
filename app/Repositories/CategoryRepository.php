<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly Category $model)
    {
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('subcategories')->latest()->paginate($perPage);
    }

    public function find(string $id): Category
    {
        return $this->model->with('subcategories')->findOrFail($id);
    }

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): Category
    {
        $Category = $this->model->findOrFail($id);
        $Category->update($data);

        return $Category;
    }

    public function delete(string $id): bool
    {
        return (bool) $this->model->findOrFail($id)->delete();
    }
}