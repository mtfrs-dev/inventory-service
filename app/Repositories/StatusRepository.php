<?php

namespace App\Repositories;

use App\Models\Status;
use App\Repositories\Contracts\StatusRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StatusRepository implements StatusRepositoryInterface
{
    public function __construct(private readonly Status $model)
    {
    }

    public function all(): Collection
    {
        return $this->model->orderBy('code')->get();
    }

    public function find(string $id): Status
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Status
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): Status
    {
        $status = $this->model->findOrFail($id);
        $status->update($data);

        return $status;
    }

    public function delete(string $id): bool
    {
        return (bool) $this->model->findOrFail($id)->delete();
    }
}