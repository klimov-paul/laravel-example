<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Favorite $resource
 */
class FavoriteResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'created_at' => (string) $this->resource->created_at,
            'updated_at' => (string) $this->resource->updated_at,
            'book' => new BookResource($this->resource->book),
        ];
    }
}
