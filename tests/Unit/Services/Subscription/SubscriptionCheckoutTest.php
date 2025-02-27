<?php

namespace Tests\Unit\Services\Subscription;

use App\Events\Subscription\UserSubscribed;
use App\Exceptions\PaymentException;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Services\Subscription\SubscriptionCheckout;
use Database\Factories\SubscriptionPlanFactory;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Payment\BraintreeMock;
use Tests\Support\Payment\BraintreeTrait;
use Tests\TestCase;

#[Group('subscription')]
#[Group('braintree')]
class SubscriptionCheckoutTest extends TestCase
{
    use BraintreeTrait;

    /**
     * @var User test user instance.
     */
    protected $user;

    /**
     * @var SubscriptionPlan test subscription plan instance.
     */
    protected $subscriptionPlan;

    protected BraintreeMock $braintree;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->braintree = $this->mockBraintree();

        $this->user = UserFactory::new()->create();

        $this->subscriptionPlan = SubscriptionPlanFactory::new()->create();
    }

    public function testProcessSuccess(): void
    {
        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan, $this->braintree);

        $subscription = $checkout->process($this->validPaymentMethodNonce());

        $this->assertEquals($this->user->id, $subscription->user_id);
        $this->assertEquals($this->subscriptionPlan->id, $subscription->subscription_plan_id);
        $this->assertNotNull($this->user->activePaymentMethod);

        $this->assertEquals(-$this->subscriptionPlan->price, $this->braintree->balances[$this->user->activePaymentMethod->token]);

        Event::assertDispatched(UserSubscribed::class, function (UserSubscribed $e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    public function testProcessNoPaymentMethod(): void
    {
        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan, $this->braintree);

        $this->expectException(PaymentException::class);

        $checkout->process();
    }

    #[Depends('testProcessSuccess')]
    public function testProcessExistingPaymentMethod(): void
    {
        PaymentMethod::createForUser($this->user, $this->braintree, $this->validPaymentMethodNonce());

        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan, $this->braintree);

        $subscription = $checkout->process();

        $this->assertEquals($this->user->id, $subscription->user_id);

        $this->assertEquals(-$this->subscriptionPlan->price, $this->braintree->balances[$this->user->activePaymentMethod->token]);
    }

    #[Depends('testProcessExistingPaymentMethod')]
    public function testUpgradeSubscription(): void
    {
        PaymentMethod::createForUser($this->user, $this->braintree, $this->validPaymentMethodNonce());

        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan, $this->braintree);
        $checkout->process();

        $newSubscriptionPlan = SubscriptionPlanFactory::new()->create([
            'price' => $this->subscriptionPlan->price * 2 + 1,
        ]);

        $checkout = new SubscriptionCheckout($this->user, $newSubscriptionPlan, $this->braintree);
        $subscription = $checkout->process();

        $this->assertEquals($newSubscriptionPlan->price - $this->subscriptionPlan->price, $subscription->payments[0]->amount);

        Event::assertDispatchedTimes(UserSubscribed::class, 1);
    }

    #[Depends('testProcessExistingPaymentMethod')]
    public function testDowngradeSubscription(): void
    {
        PaymentMethod::createForUser($this->user, $this->braintree, $this->validPaymentMethodNonce());

        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan, $this->braintree);
        $checkout->process();

        $newSubscriptionPlan = SubscriptionPlanFactory::new()->create([
            'price' => $this->subscriptionPlan->price / 2,
        ]);

        $checkout = new SubscriptionCheckout($this->user, $newSubscriptionPlan, $this->braintree);
        $subscription = $checkout->process();

        $this->assertCount(0, $subscription->payments);

        Event::assertDispatchedTimes(UserSubscribed::class, 1);
    }
}
