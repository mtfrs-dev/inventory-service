<?php

namespace App\Repositories\Contracts;

use App\Models\Item;
use Illuminate\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function find(string $id): Item;

    public function create(array $data): Item;

    public function update(string $id, array $data): Item;

    public function delete(string $id): bool;
}
