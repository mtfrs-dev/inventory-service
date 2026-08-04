<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\ItemAttachmentResource;
use App\Http\Resources\StatusResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'project_id'        => $this->project_id,
            'category_id'       => $this->category_id,
            'subcategory_id'    => $this->subcategory_id,
            'serial_number'     => $this->serial_number,
            'code'              => $this->code,
            'name'              => $this->name,
            'description'       => $this->description,
            'qr_code_url'       => $this->qr_code ? asset('storage/'.$this->qr_code) : null,
            'current_status'    => StatusResource::make($this->whenLoaded('currentStatus')),
            'statuses'          => StatusResource::collection($this->whenLoaded('statuses')),
            'attachments'       => ItemAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,
        ];
    }
}