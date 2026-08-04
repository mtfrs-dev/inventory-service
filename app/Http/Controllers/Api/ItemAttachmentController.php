<?php

namespace App\Http\Controllers\Api;

use App\Actions\Item\DeleteItemAttachmentAction;
use App\Actions\Item\StoreItemAttachmentAction;
use App\Exceptions\Api\ResourceNotFoundException;
use App\Http\Requests\Item\StoreItemAttachmentRequest;
use App\Http\Requests\Item\UpdateItemAttachmentRequest;
use App\Http\Resources\ItemAttachmentResource;
use App\Models\ItemAttachment;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ItemAttachmentController extends ApiController
{
    public function __construct(private readonly ItemRepositoryInterface $items) {}

    public function store(StoreItemAttachmentRequest $request, string $itemId, StoreItemAttachmentAction $action): JsonResponse
    {
        $item = $this->items->find($itemId);

        $action->handle($item, $request->file('attachments'));

        $item->load('attachments');

        return $this->created(
            ItemAttachmentResource::collection($item->attachments),
            'Attachments added successfully'
        );
    }

    public function update(UpdateItemAttachmentRequest $request, string $itemId, string $attachmentId): JsonResponse
    {
        $attachment = $this->resolveAttachment($itemId, $attachmentId);

        $attachment->update($request->validated());

        return $this->success(
            new ItemAttachmentResource($attachment),
            'Attachment updated successfully'
        );
    }

    public function destroy(string $itemId, string $attachmentId, DeleteItemAttachmentAction $action): Response
    {
        $action->handle($this->resolveAttachment($itemId, $attachmentId));

        return response()->noContent();
    }

    private function resolveAttachment(string $itemId, string $attachmentId): ItemAttachment
    {
        return ItemAttachment::where('id', $attachmentId)
            ->where('item_id', $itemId)
            ->firstOr(fn () => throw new ResourceNotFoundException('Attachment not found.'));
    }
}
