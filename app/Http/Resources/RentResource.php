<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Rent $resource
 */
class RentResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'begin_at' => (string) $this->resource->begin_at,
            'due_at' => (string) $this->resource->due_at,
            'end_at' => $this->resource->end_at ? (string) $this->resource->end_at : null,
            'created_at' => (string) $this->resource->created_at,
            'updated_at' => (string) $this->resource->updated_at,
            'book' => new BookResource($this->resource->book),
        ];
    }
}
