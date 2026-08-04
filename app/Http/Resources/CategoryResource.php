<?php

namespace App\Http\Resources;

use App\Http\Resources\SubcategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'project_id'        => $this->project_id,
            'code'              => $this->code,
            'name'              => $this->name,
            'description'       => $this->description,
            'subcategories'     => SubcategoryResource::collection($this->whenLoaded('subcategories')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at
        ];
    }
}