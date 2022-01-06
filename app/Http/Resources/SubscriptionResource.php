<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Subscription $resource
 */
class SubscriptionResource extends JsonResource
{
    /**
     * {@inheritdoc}
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'subscription_plan_id' => $this->resource->subscription_plan_id,
            'status' => $this->resource->status,
            'is_recurrent' => $this->resource->is_recurrent,
            'created_at' => (string) $this->resource->created_at,
            'begin_at' => (string) $this->resource->begin_at,
            'end_at' => (string) $this->resource->end_at,
            //'subscription_plan' => new SubscriptionPlanResource($this->resource->subscriptionPlan),
        ];
    }
}
