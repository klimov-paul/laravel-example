<?php

namespace Tests\Unit\Services\Subscription;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Events\Subscription\UserSubscriptionTerminated;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\SubscriptionPlan;
use App\Notifications\Subscription\SubscriptionEnded;
use App\Notifications\Subscription\SubscriptionProlongationCancelled;
use App\Notifications\Subscription\SubscriptionProlongationNoPaymentMethodFailure;
use App\Notifications\Subscription\SubscriptionProlongationSucceed;
use App\Notifications\Subscription\SubscriptionProlongationPaymentFailure;
use App\Services\Subscription\SubscriptionProlonger;
use Database\Factories\PaymentMethodFactory;
use Database\Factories\PaymentFactory;
use Database\Factories\SubscriptionPlanFactory;
use Database\Factories\UserFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Payment\BraintreeMock;
use Tests\Support\Payment\BraintreeTrait;
use Tests\TestCase;

#[Group('subscription')]
#[Group('braintree')]
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

    protected BraintreeMock $braintree;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->braintree = $this->mockBraintree();

        Notification::fake();

        Event::fake();

        $this->user = UserFactory::new()->create();

        $this->subscriptionPlan = SubscriptionPlanFactory::new()->create();
    }

    protected function createUserPaymentMethod(): PaymentMethod
    {
        return PaymentMethod::createForUser($this->user, $this->braintree, $this->validPaymentMethodNonce());
    }

    public function testExpiredNotRecurrent(): void
    {
        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $subscription->update([
            'begin_at' => Carbon::yesterday(),
            'end_at' => Carbon::yesterday(),
            'is_recurrent' => false,
        ]);

        $handler = new SubscriptionProlonger($subscription, $this->braintree);
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

    public function testExpiredRecurrent(): void
    {
        $this->createUserPaymentMethod();

        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $subscription->update([
            'begin_at' => Carbon::yesterday(),
            'end_at' => Carbon::yesterday(),
            'is_recurrent' => true,
        ]);

        $handler = new SubscriptionProlonger($subscription, $this->braintree);
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

    #[Depends('testExpiredRecurrent')]
    public function testNoPaymentMethod(): void
    {
        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $subscription->update([
            'begin_at' => Carbon::yesterday(),
            'end_at' => Carbon::yesterday(),
            'is_recurrent' => true,
        ]);

        $handler = new SubscriptionProlonger($subscription, $this->braintree);
        $handler->handleExpiration();

        $subscriptions = $this->user->subscriptions()->orderBy('id')->get();

        $this->assertCount(1, $subscriptions);

        $subscription = $subscriptions[0];

        $this->assertEquals(SubscriptionStatus::ARCHIVED, $subscription->status);

        Notification::assertSentTo($this->user, SubscriptionProlongationNoPaymentMethodFailure::class);

        Event::assertDispatched(UserSubscriptionTerminated::class, function (UserSubscriptionTerminated $e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    #[Depends('testExpiredRecurrent')]
    public function testExpiredRecurrentFail(): void
    {
        PaymentMethodFactory::new()->create([
            'user_id' => $this->user->id,
        ]);

        $subscription = $this->subscriptionPlan->subscribe($this->user);

        $subscription->update([
            'begin_at' => Carbon::yesterday(),
            'end_at' => Carbon::yesterday(),
            'is_recurrent' => true,
        ]);

        $handler = new SubscriptionProlonger($subscription, $this->braintree);
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

    public function testActivatePending(): void
    {
        $this->createUserPaymentMethod();

        $subscription = $this->subscriptionPlan->subscribe($this->user, 1, [
            'status' => SubscriptionStatus::PENDING,
        ]);

        $handler = new SubscriptionProlonger($subscription, $this->braintree);
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

    #[Depends('testExpiredRecurrent')]
    public function testMaxPaymentAttemptReach(): void
    {
        $paymentMethod = PaymentMethodFactory::new()->create([
            'user_id' => $this->user->id,
        ]);

        $subscription = $this->subscriptionPlan->subscribe($this->user, 1, [
            'status' => SubscriptionStatus::PENDING,
        ]);

        for ($attemptCount = 1; $attemptCount < SubscriptionProlonger::MAX_PAYMENT_ATTEMPT_COUNT; $attemptCount++) {
            $payment = PaymentFactory::new()->create([
                'payment_method_id' => $paymentMethod->id,
                'status' => PaymentStatus::FAILED,
            ]);
            $subscription->payments()->attach($payment->id);
        }

        $handler = new SubscriptionProlonger($subscription, $this->braintree);
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
