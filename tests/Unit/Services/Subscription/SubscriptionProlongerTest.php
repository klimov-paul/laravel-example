<?php

namespace Tests\Unit\Services\Subscription;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Events\Subscription\UserSubscriptionTerminated;
use App\Models\User;
use App\Models\CreditCard;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Notifications\Subscription\SubscriptionEnded;
use App\Notifications\Subscription\SubscriptionProlongationCancelled;
use App\Notifications\Subscription\SubscriptionProlongationNoCreditCardFailure;
use App\Notifications\Subscription\SubscriptionProlongationSucceed;
use App\Notifications\Subscription\SubscriptionProlongationPaymentFailure;
use App\Services\Subscription\SubscriptionProlonger;
use Database\Factories\CreditCardFactory;
use Database\Factories\PaymentFactory;
use Database\Factories\SubscriptionPlanFactory;
use Database\Factories\UserFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Support\Payment\BraintreeTrait;
use Tests\TestCase;

/**
 * @group subscription
 * @group braintree
 */
class SubscriptionProlongerTest extends TestCase
{
    use BraintreeTrait;

    /**
     * @var User test user instance.
     */
    protected User $user;

    /**
     * @var SubscriptionPlan test subscription plan instance.
     */
    protected $subscriptionPlan;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockBraintree();

        Notification::fake();

        Event::fake();

        $this->user = UserFactory::new()->create();

        $this->subscriptionPlan = SubscriptionPlanFactory::new()->create();
    }

    protected function createUserCreditCard(): CreditCard
    {
        return (new CreditCard())->createForUser($this->user, $this->validCreditCardToken());
    }

    public function testExpiredNotRecurrent()
    {
        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $subscription->update([
            'begin_at' => Carbon::yesterday(),
            'end_at' => Carbon::yesterday(),
            'is_recurrent' => false,
        ]);

        $handler = new SubscriptionProlonger($subscription);
        $handler->handleExpiration();

        $subscriptions = $this->user->subscriptions()->orderBy('id')->get();

        $this->assertCount(1, $subscriptions);

        $subscription = $subscriptions[0];

        $this->assertEquals(SubscriptionStatus::ARCHIVED, $subscription->status);

        Notification::assertSentTo($this->user, SubscriptionEnded::class);

        Event::assertDispatched(UserSubscriptionTerminated::class, function (UserSubscriptionTerminated $e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    public function testExpiredRecurrent()
    {
        $this->createUserCreditCard();

        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $subscription->update([
            'begin_at' => Carbon::yesterday(),
            'end_at' => Carbon::yesterday(),
            'is_recurrent' => true,
        ]);

        $handler = new SubscriptionProlonger($subscription);
        $handler->handleExpiration();

        $subscriptions = $this->user->subscriptions()->orderBy('id')->get();

        $this->assertCount(2, $subscriptions);

        $this->assertEquals(SubscriptionStatus::ARCHIVED, $subscriptions[0]->status);
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscriptions[1]->status);

        $this->assertCount(1, $subscriptions[1]->payments);
        $this->assertEquals(PaymentStatus::SUCCESS, $subscriptions[1]->payments[0]->status);
        $this->assertEquals($this->subscriptionPlan->price, $subscriptions[1]->payments[0]->amount);

        Notification::assertSentTo($this->user, SubscriptionProlongationSucceed::class);

        Event::assertNotDispatched(UserSubscriptionTerminated::class);
    }

    /**
     * @depends testExpiredRecurrent
     */
    public function testNoCreditCard()
    {
        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $subscription->update([
            'begin_at' => Carbon::yesterday(),
            'end_at' => Carbon::yesterday(),
            'is_recurrent' => true,
        ]);

        $handler = new SubscriptionProlonger($subscription);
        $handler->handleExpiration();

        $subscriptions = $this->user->subscriptions()->orderBy('id')->get();

        $this->assertCount(1, $subscriptions);

        $subscription = $subscriptions[0];

        $this->assertEquals(SubscriptionStatus::ARCHIVED, $subscription->status);

        Notification::assertSentTo($this->user, SubscriptionProlongationNoCreditCardFailure::class);

        Event::assertDispatched(UserSubscriptionTerminated::class, function (UserSubscriptionTerminated $e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    /**
     * @depends testExpiredRecurrent
     */
    public function testExpiredRecurrentFail()
    {
        CreditCardFactory::new()->create([
            'user_id' => $this->user->id,
        ]);

        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $subscription->update([
            'begin_at' => Carbon::yesterday(),
            'end_at' => Carbon::yesterday(),
            'is_recurrent' => true,
        ]);

        $handler = new SubscriptionProlonger($subscription);
        $handler->handleExpiration();

        $subscriptions = $this->user->subscriptions()->orderBy('id')->get();

        $this->assertCount(2, $subscriptions);

        $this->assertEquals(SubscriptionStatus::ARCHIVED, $subscriptions[0]->status);
        $this->assertEquals(SubscriptionStatus::PENDING, $subscriptions[1]->status);

        $this->assertCount(1, $subscriptions[1]->payments);
        $this->assertEquals(PaymentStatus::FAILED, $subscriptions[1]->payments[0]->status);
        $this->assertEquals($this->subscriptionPlan->price, $subscriptions[1]->payments[0]->amount);

        Notification::assertSentTo($this->user, SubscriptionProlongationPaymentFailure::class);

        Event::assertNotDispatched(UserSubscriptionTerminated::class);
    }

    public function testActivatePending()
    {
        $this->createUserCreditCard();

        $subscription = $this->subscriptionPlan->subscribe($this->user, 1, [
            'status' => SubscriptionStatus::PENDING,
        ]);

        $handler = new SubscriptionProlonger($subscription);
        $handler->handlePending();

        $subscriptions = $this->user->subscriptions()->orderBy('id')->get();

        $this->assertCount(1, $subscriptions);

        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscriptions[0]->status);

        $this->assertCount(1, $subscriptions[0]->payments);
        $this->assertEquals(PaymentStatus::SUCCESS, $subscriptions[0]->payments[0]->status);
        $this->assertEquals($this->subscriptionPlan->price, $subscriptions[0]->payments[0]->amount);

        Notification::assertSentTo($this->user, SubscriptionProlongationSucceed::class);

        Event::assertNotDispatched(UserSubscriptionTerminated::class);
    }

    /**
     * @depends testActivatePending
     */
    public function testMaxPaymentAttemptReach()
    {
        $creditCard = CreditCardFactory::new()->create([
            'user_id' => $this->user->id,
        ]);

        $subscription = $this->subscriptionPlan->subscribe($this->user, 1, [
            'status' => SubscriptionStatus::PENDING,
        ]);

        for ($attemptCount = 1; $attemptCount < SubscriptionProlonger::MAX_PAYMENT_ATTEMPT_COUNT; $attemptCount++) {
            $payment = PaymentFactory::new()->create([
                'credit_card_id' => $creditCard->id,
                'status' => PaymentStatus::FAILED,
            ]);
            $subscription->payments()->attach($payment->id);
        }

        $handler = new SubscriptionProlonger($subscription);
        $handler->handlePending();

        $subscriptions = $this->user->subscriptions()->orderBy('id')->get();

        $this->assertCount(1, $subscriptions);

        $subscription = $subscriptions[0];

        $this->assertEquals(SubscriptionStatus::CANCELLED, $subscription->status);

        $this->assertCount(SubscriptionProlonger::MAX_PAYMENT_ATTEMPT_COUNT, $subscription->payments);

        $lastPayment = $subscription->payments()->orderBy('id', 'desc')->first();

        $this->assertEquals(PaymentStatus::FAILED, $lastPayment->status);
        $this->assertEquals($this->subscriptionPlan->price, $lastPayment->amount);

        Notification::assertSentTo($this->user, SubscriptionProlongationCancelled::class);

        Event::assertDispatched(UserSubscriptionTerminated::class, function (UserSubscriptionTerminated $e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }
}
