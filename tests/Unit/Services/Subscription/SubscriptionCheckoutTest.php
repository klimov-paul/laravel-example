<?php

namespace Tests\Unit\Services\Subscription;

use App\Events\Subscription\UserSubscribed;
use App\Exceptions\PaymentException;
use App\Models\CreditCard;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Services\Subscription\SubscriptionCheckout;
use Database\Factories\SubscriptionPlanFactory;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Event;
use Tests\Support\Payment\BraintreeMock;
use Tests\Support\Payment\BraintreeTrait;
use Tests\TestCase;

/**
 * @group subscription
 * @group braintree
 */
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

    protected BraintreeMock $paymentGatewayMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->paymentGatewayMock = $this->mockBraintree();

        $this->user = UserFactory::new()->create();

        $this->subscriptionPlan = SubscriptionPlanFactory::new()->create();
    }

    public function testProcessSuccess()
    {
        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan);

        $subscription = $checkout->process($this->validCreditCardToken());

        $this->assertEquals($this->user->id, $subscription->user_id);
        $this->assertEquals($this->subscriptionPlan->id, $subscription->subscription_plan_id);
        $this->assertNotNull($this->user->activeCreditCard);

        $this->assertEquals(-$this->subscriptionPlan->price, $this->paymentGatewayMock->balances[$this->user->activeCreditCard->external_id]);

        Event::assertDispatched(UserSubscribed::class, function (UserSubscribed $e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    public function testProcessNoCreditCard()
    {
        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan);

        $this->expectException(PaymentException::class);

        $checkout->process();
    }

    /**
     * @depends testProcessSuccess
     */
    public function testProcessExistingCreditCard()
    {
        (new CreditCard())->createForUser($this->user, $this->validCreditCardToken());

        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan);

        $subscription = $checkout->process();

        $this->assertEquals($this->user->id, $subscription->user_id);

        $this->assertEquals(-$this->subscriptionPlan->price, $this->paymentGatewayMock->balances[$this->user->activeCreditCard->external_id]);
    }

    /**
     * @depends testProcessExistingCreditCard
     */
    public function testUpgradeSubscription()
    {
        (new CreditCard())->createForUser($this->user, $this->validCreditCardToken());

        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan);
        $checkout->process();

        $newSubscriptionPlan = SubscriptionPlanFactory::new()->create([
            'price' => $this->subscriptionPlan->price * 2 + 1,
        ]);

        $checkout = new SubscriptionCheckout($this->user, $newSubscriptionPlan);
        $subscription = $checkout->process();

        $this->assertEquals($newSubscriptionPlan->price - $this->subscriptionPlan->price, $subscription->payments[0]->amount);

        Event::assertDispatchedTimes(UserSubscribed::class, 1);
    }

    /**
     * @depends testProcessExistingCreditCard
     */
    public function testDowngradeSubscription()
    {
        (new CreditCard())->createForUser($this->user, $this->validCreditCardToken());

        $checkout = new SubscriptionCheckout($this->user, $this->subscriptionPlan);
        $checkout->process();

        $newSubscriptionPlan = SubscriptionPlanFactory::new()->create([
            'price' => $this->subscriptionPlan->price / 2,
        ]);

        $checkout = new SubscriptionCheckout($this->user, $newSubscriptionPlan);
        $subscription = $checkout->process();

        $this->assertCount(0, $subscription->payments);

        Event::assertDispatchedTimes(UserSubscribed::class, 1);
    }
}
