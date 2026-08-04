<?php

namespace App\Repositories\Contracts;

use App\Models\Status;
use Illuminate\Database\Eloquent\Collection;

interface StatusRepositoryInterface
{
    public function all(): Collection;

    public function find(string $id): Status;

    public function create(array $data): Status;

    public function update(string $id, array $data): Status;

    public function delete(string $id): bool;
}