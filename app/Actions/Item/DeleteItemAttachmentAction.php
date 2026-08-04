<?php

namespace App\Actions\Item;

use App\Models\ItemAttachment;
use Illuminate\Support\Facades\Storage;

class DeleteItemAttachmentAction
{
    public function handle(ItemAttachment $attachment): void
    {
        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();
    }
}
