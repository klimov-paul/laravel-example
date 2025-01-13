<?php

namespace Tests\Feature\Me;

use App\Models\User;
use App\Models\SubscriptionPlan;
use Database\Factories\SubscriptionPlanFactory;
use Database\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Payment\BraintreeTrait;
use Tests\TestCase;

#[Group('subscription')]
#[Group('braintree')]
class SubscriptionsTest extends TestCase
{
    use BraintreeTrait;

    protected User $user;

    protected SubscriptionPlan $subscriptionPlan;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockBraintree();

        $this->user = UserFactory::new()->create();

        $this->subscriptionPlan = SubscriptionPlanFactory::new()->create();
    }

    public function testSubscribe(): void
    {
        $this->actingAs($this->user);

        $this->postJson(route('api.me.subscriptions.store'), [
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'nonce' => 'fake-valid-visa-nonce',
            'accept_terms' => true,
        ])
            ->assertSuccessful()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'subscription' => [
                        'id',
                        'user_id',
                        'subscription_plan_id',
                        'is_recurrent',
                        'begin_at',
                        'end_at',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'subscription' => [
                        'user_id' => $this->user->id,
                        'subscription_plan_id' => $this->subscriptionPlan->id,
                        'is_recurrent' => true,
                    ],
                ],
            ]);

        $this->assertNotNull($this->user->activeSubscription);
    }

    #[Depends('testSubscribe')]
    public function testHistory(): void
    {
        $this->actingAs($this->user);

        $this->subscriptionPlan->subscribe($this->user);

        $this->getJson(route('api.me.subscriptions.index'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'user_id',
                        'subscription_plan_id',
                        'is_recurrent',
                        'begin_at',
                        'end_at',
                    ],
                ],
            ]);
    }

    #[Depends('testSubscribe')]
    public function testShow(): void
    {
        $this->actingAs($this->user);

        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $this->getJson(route('api.me.subscriptions.show', [$subscription]))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'subscription_plan_id',
                    'is_recurrent',
                    'begin_at',
                    'end_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $subscription->id,
                ],
            ]);
    }

    #[Depends('testSubscribe')]
    public function testSubscribeErrorNoPaymentMethod(): void
    {
        $this->actingAs($this->user);

        $this->postJson(route('api.me.subscriptions.store'), [
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'accept_terms' => true,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertNull($this->user->activeSubscription);
    }
}
