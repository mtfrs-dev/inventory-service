<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'item_id'     => $this->item_id,
            'name'        => $this->name,
            'description' => $this->description,
            'url'         => asset('storage/'.$this->path),
            'mime_type'   => $this->mime_type,
            'size'        => $this->size,
            'created_at'  => $this->created_at,
        ];
    }
}
