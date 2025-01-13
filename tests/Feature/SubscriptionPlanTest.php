<?php

namespace Tests\Feature;

use Database\Factories\SubscriptionPlanFactory;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\SubscriptionPlanController
 */
class SubscriptionPlanTest extends TestCase
{
    public function testIndex(): void
    {
        $subscriptionPlans = SubscriptionPlanFactory::new()->count(2)->create();

        $this->getJson(route('api.subscriptionPlans.index', ['sort' => '-id']))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'description',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    [
                        'id' => $subscriptionPlans[1]->id,
                    ],
                    [
                        'id' => $subscriptionPlans[0]->id,
                    ],
                ],
            ]);
    }

    public function testShow(): void
    {
        $subscriptionPlan = SubscriptionPlanFactory::new()->create();

        $this->getJson(route('api.subscriptionPlans.show', [$subscriptionPlan]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => $subscriptionPlan->id,
                    'name' => $subscriptionPlan->name,
                    'description' => $subscriptionPlan->description,
                    'price' => $subscriptionPlan->price,
                    'max_rent_count' => $subscriptionPlan->max_rent_count,
                    'max_book_price' => $subscriptionPlan->max_book_price,
                ],
            ]);
    }
}
