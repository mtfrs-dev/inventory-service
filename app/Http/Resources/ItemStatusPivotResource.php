<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemStatusPivotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'previous_status_id'    => $this->previous_status_id,
            'updated_by'            => $this->updated_by,
            'reason'                => $this->reason,
            'location_name'         => $this->location_name,
            'location_address'      => $this->location_address,
            'effective_at'          => $this->effective_at,
        ];
    }
}