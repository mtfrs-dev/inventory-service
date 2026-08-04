<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps a plain array (reports are not persisted Eloquent entities).
 */
class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->resource['type'],
            'generated_at' => $this->resource['generated_at'],
            'period' => $this->resource['period'],
            'data' => $this->resource['data'],
        ];
    }
}
