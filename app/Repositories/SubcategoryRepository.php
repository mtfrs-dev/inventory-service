<?php

namespace App\Repositories;

use App\Models\Subcategory;
use App\Repositories\Contracts\SubcategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SubcategoryRepository implements SubcategoryRepositoryInterface
{
    public function __construct(private readonly Subcategory $model)
    {
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('category')->latest()->paginate($perPage);
    }

    public function find(string $id): Subcategory
    {
        return $this->model->with('category')->findOrFail($id);
    }

    public function create(array $data): Subcategory
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): Subcategory
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