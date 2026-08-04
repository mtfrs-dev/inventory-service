<?php

namespace App\Http\Controllers\Api;

use App\Actions\Item\BulkUpdateItemSerialNumberAction;
use App\Actions\Item\BulkUpdateItemStatusAction;
use App\Actions\Item\EnsureEntitySyncedAction;
use App\Actions\Item\ValidateItemGenerationCapacityAction;
use App\Http\Requests\Item\BulkUpdateItemSerialNumberRequest;
use App\Http\Requests\Item\BulkUpdateItemStatusRequest;
use App\Http\Requests\Item\FilterItemsRequest;
use App\Http\Requests\Item\GenerateItemsRequest;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Jobs\Item\GenerateItemsByCategoryJob;
use App\Jobs\Item\GenerateItemsBySubcategoryJob;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use RuntimeException;

class ItemController extends ApiController
{
    public function __construct(private readonly ItemRepositoryInterface $items) {}

    public function index(FilterItemsRequest $request): JsonResponse
    {
        return $this->paginated(
            ItemResource::collection($this->items->paginate($request->validated())),
            'Items retrieved successfully'
        );
    }

    public function show(string $id): JsonResponse
    {
        return $this->success(
            new ItemResource($this->items->find($id)),
            'Item retrieved successfully'
        );
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        return $this->created(
            new ItemResource($this->items->create($request->validated())),
            'Item created successfully'
        );
    }

    public function generate(
        GenerateItemsRequest $request,
        EnsureEntitySyncedAction $ensureEntitySync,
        ValidateItemGenerationCapacityAction $capacityValidator,
    ): JsonResponse {
        $entityType         = $request->input('entity_type'); // category/subcategory | spektek/subspektek

        // entity: category|spektek -> parent: project
        // entity: subcategory|subspektek -> parent: category
        $parentId           = $request->input('parent_id');
        $externalParentId   = $request->filled('external_parent_id')
            ? $request->integer('external_parent_id')
            : null;

        $entityId           = $request->input('entity_id');
        $externalEntityId = $request->filled('external_entity_id')
            ? $request->integer('external_entity_id')
            : null;

        $itemsCount = $request->integer('items_count');

        try {
            $model = $ensureEntitySync->handle(
                $entityType,
                $parentId,
                $externalParentId,
                $entityId,
                $externalEntityId,
            );

            if ($entityType === 'category') {
                $capacityValidator->forCategory($model, $itemsCount, []);
                GenerateItemsByCategoryJob::dispatch($model, $itemsCount, []);
            } else {
                $capacityValidator->forSubcategory($model, $itemsCount);
                GenerateItemsBySubcategoryJob::dispatch($model, $itemsCount);
            }
        } catch (ModelNotFoundException) {
            $resource = $entityType === 'category' ? 'Kategori' : 'Subkategori';
            return $this->error("{$resource} tidak ditemukan.", 404);
        } catch (RequestException $e) {
            return $this->error('Tidak dapat menghubungi layanan PM: '.$e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 503);
        }

        $resource = $entityType === 'category' ? 'kategori' : 'subkategori';

        return $this->success(message: "Pembuatan item untuk {$resource} telah dijadwalkan", code: 202);
    }

    public function bulkUpdateStatus(BulkUpdateItemStatusRequest $request, BulkUpdateItemStatusAction $action): JsonResponse
    {
        return $this->success(
            ItemResource::collection($action->handle($request->validated('items'))),
            'Item statuses updated successfully'
        );
    }

    public function bulkUpdateSerialNumber(BulkUpdateItemSerialNumberRequest $request, BulkUpdateItemSerialNumberAction $action): JsonResponse
    {
        return $this->success(
            ItemResource::collection($action->handle($request->validated('items'))),
            'Serial numbers berhasil diupdate'
        );
    }

    public function update(UpdateItemRequest $request, string $id): JsonResponse
    {
        return $this->success(
            new ItemResource($this->items->update($id, $request->validated())),
            'Serial Number Item berhasil diupdate'
        );
    }

    public function destroy(string $id): Response
    {
        $this->items->delete($id);

        return response()->noContent();
    }

    public function qrCode(string $id): JsonResponse
    {
        $item = $this->items->find($id);

        if (! $item->qr_code) {
            return $this->error('QR code not yet generated for this item.', 404);
        }

        return $this->success(
            data: ['qr_code_url' => asset('storage/'.$item->qr_code)],
            message: 'QR code retrieved successfully',
        );
    }
}
