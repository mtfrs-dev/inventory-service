<?php

namespace App\Http\Resources;

use App\Http\Resources\ItemStatusPivotResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'label'         => $this->label,
            'description'   => $this->description,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'pivot'         => $this->when(
                $this->pivot,
                fn () => new ItemStatusPivotResource($this->pivot)
            ),

        ];
    }
}