<?php

namespace App\Actions\Item;

use App\Models\Item;
use App\Models\ItemAttachment;
use Illuminate\Http\UploadedFile;

class StoreItemAttachmentAction
{
    public function handle(Item $item, array $files): void
    {
        foreach ($files as $file) {
            /** @var UploadedFile $file */
            ItemAttachment::create([
                'item_id'   => $item->id,
                'name'      => $file->getClientOriginalName(),
                'path'      => $file->store("item-attachments/{$item->id}", 'public'),
                'mime_type' => $file->getMimeType(),
                'size'      => $file->getSize(),
            ]);
        }
    }
}
