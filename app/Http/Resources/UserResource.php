<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * UserResource is a JSON resource for `User` model.
 *
 * @property \App\Models\User $resource
 */
class UserResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'email' => $this->resource->email,
            'name' => $this->resource->name,
            'email_verified_at' => (string) $this->resource->email_verified_at,
            'created_at' => (string) $this->resource->created_at,
            'updated_at' => (string) $this->resource->updated_at,
        ];
    }
}
