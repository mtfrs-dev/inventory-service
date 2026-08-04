<?php

namespace App\Repositories;

use App\Exceptions\Api\DuplicateEntryException;
use App\Exceptions\Api\ResourceNotFoundException;
use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemRepository implements ItemRepositoryInterface
{
    public function __construct(private readonly Item $model) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage   = (int) ($filters['per_page'] ?? 15);
        $sortCol   = $filters['sort'] ?? 'code';
        $direction = $filters['direction'] ?? 'asc';

        return $this->model
            ->with('currentStatus')
            ->when(
                $filters['trashed'] ?? null,
                fn ($q, $v) => $v === 'only' ? $q->onlyTrashed() : $q->withTrashed()
            )
            ->when(
                $filters['project_id'] ?? null,
                fn ($q, $v) => $q->where('project_id', $v)
            )
            ->when(
                $filters['external_project_id'] ?? null,
                fn ($q, $v) => $q->whereHas('project', fn ($pq) => $pq->where('external_id', $v))
            )
            ->when(
                $filters['category_id'] ?? null,
                fn ($q, $v) => $q->where('category_id', $v)
            )
            ->when(
                $filters['external_category_id'] ?? null,
                fn ($q, $v) => $q->whereHas('category', fn ($cq) => $cq->where('external_id', $v))
            )
            ->when(
                $filters['subcategory_id'] ?? null,
                fn ($q, $v) => $q->where('subcategory_id', $v)
            )
            ->when(
                $filters['external_subcategory_id'] ?? null,
                fn ($q, $v) => $q->whereHas('subcategory', fn ($sq) => $sq->where('external_id', $v))
            )
            ->when(
                $filters['status'] ?? null,
                fn ($q, $v) => $q->whereHas('currentStatus', fn ($sq) => $sq->where('code', $v))
            )
            ->when(
                $filters['search'] ?? null,
                fn ($q, $v) => $q->where(
                    fn ($sq) => $sq->where('code', 'like', "%{$v}%")
                        ->orWhere('serial_number', 'like', "%{$v}%")
                )
            )
            ->when(
                $filters['user_id'] ?? null,
                fn ($q, $v) => $q->whereHas('statuses', fn ($sq) => $sq->where('item_status.updated_by', $v))
            )
            ->orderBy($sortCol, $direction)
            ->paginate($perPage);
    }

    public function find(string $id): Item
    {
        try {
            return $this->model->with(['currentStatus', 'statuses', 'attachments'])->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ResourceNotFoundException('Item with given ID not found.');
        }
    }

    public function create(array $data): Item
    {
        try {
            $item = $this->model->create($data);
            $item->load('currentStatus');

            return $item;
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                throw new DuplicateEntryException('An item with this code or serial number already exists.');
            }
            throw $e;
        }
    }

    public function update(string $id, array $data): Item
    {
        try {
            $item = $this->model->findOrFail($id);
            $item->update($data);

            return $item;
        } catch (ModelNotFoundException) {
            throw new ResourceNotFoundException('Item with given ID not found.');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                throw new DuplicateEntryException('An item with this code or serial number already exists.');
            }
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            return (bool) $this->model->findOrFail($id)->delete();
        } catch (ModelNotFoundException) {
            throw new ResourceNotFoundException('Item with given ID not found.');
        }
    }
}
