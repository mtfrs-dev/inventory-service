<?php

namespace App\Actions\Item;

use App\Models\Item;
use App\Models\ItemStatus;
use App\Models\ItemStatusAttachment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BulkUpdateItemStatusAction
{
    public function handle(array $updates): Collection
    {
        return DB::transaction(function () use ($updates) {
            $ids   = array_column($updates, 'id');
            $items = Item::whereIn('id', $ids)->get()->keyBy('id');
            $now   = now();

            foreach ($updates as $update) {
                $item     = $items[$update['id']];
                $statusId = $update['status_id'];

                ItemStatus::where('item_id', $item->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'ineffective_at' => $now]);

                $itemStatus = ItemStatus::create([
                    'item_id'            => $item->id,
                    'status_id'          => $statusId,
                    'previous_status_id' => $item->current_status_id,
                    'is_active'          => true,
                    'effective_at'       => $now,
                    'updated_by'         => $update['updated_by'] ?? null,
                    'reason'             => $update['reason'] ?? null,
                    'location_name'      => $update['location_name'] ?? null,
                    'location_address'   => $update['location_address'] ?? null,
                ]);

                foreach ($update['attachments'] ?? [] as $file) {
                    /** @var UploadedFile $file */
                    ItemStatusAttachment::create([
                        'item_status_id' => $itemStatus->id,
                        'name'           => $file->getClientOriginalName(),
                        'description'    => '',
                        'path'           => $file->store("item-status-attachments/{$itemStatus->id}", 'public'),
                        'mime_type'      => $file->getMimeType(),
                        'size'           => $file->getSize(),
                    ]);
                }

                $item->current_status_id = $statusId;
                $item->save();
            }

            return Item::with('currentStatus')->whereIn('id', $ids)->get();
        });
    }
}
