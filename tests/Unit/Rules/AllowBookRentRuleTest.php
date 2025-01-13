<?php

namespace Tests\Unit\Rules;

use App\Models\Book;
use App\Models\User;
use App\Rules\AllowBookRentRule;
use Database\Factories\BookFactory;
use Database\Factories\SubscriptionPlanFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;

class AllowBookRentRuleTest extends TestCase
{
    protected User $user;

    protected Book $book;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::new()->create();

        $this->book = BookFactory::new()->create();
    }

    public function testBookDoesNotExist(): void
    {
        $rule = new AllowBookRentRule($this->user);

        $this->book->delete();

        $this->assertFalse($rule->passes('book', $this->book->id));
    }

    public function testNoSubscription(): void
    {
        $rule = new AllowBookRentRule($this->user);

        $this->assertFalse($rule->passes('book', $this->book->id));
    }

    public function testDisallowedBySubscriptionPlan(): void
    {
        $subscriptionPlan = SubscriptionPlanFactory::new()->create([
            'max_book_price' => $this->book->price - 1,
        ]);

        $subscriptionPlan->subscribe($this->user);

        $rule = new AllowBookRentRule($this->user);

        $this->assertFalse($rule->passes('book', $this->book->id));
    }

    public function testNoAvailableRentSlots(): void
    {
        $subscriptionPlan = SubscriptionPlanFactory::new()->create([
            'max_book_price' => $this->book->price + 1,
            'max_rent_count' => 1,
        ]);

        $subscriptionPlan->subscribe($this->user);

        $extraBook = BookFactory::new()->create();
        $extraBook->rent($this->user);

        $rule = new AllowBookRentRule($this->user);

        $this->assertFalse($rule->passes('book', $this->book->id));
    }

    public function testAllow(): void
    {
        $subscriptionPlan = SubscriptionPlanFactory::new()->create([
            'max_book_price' => $this->book->price + 1,
        ]);

        $subscriptionPlan->subscribe($this->user);

        $rule = new AllowBookRentRule($this->user);

        $this->assertTrue($rule->passes('book', $this->book->id));
        $this->assertSame($this->book->id, $rule->getBook()->id);
    }
}
