<?php

namespace Tests\Unit\Models;

use Database\Factories\BookFactory;
use Database\Factories\CategoryFactory;
use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SubscriptionPlanTest extends TestCase
{
    use DatabaseTransactions;

    public function testAllowBook()
    {
        $allowedCategory = CategoryFactory::new()->create();
        $disallowedCategory = CategoryFactory::new()->create();

        $subscriptionPlan = SubscriptionPlanFactory::new()->create([
            'max_book_price' => 500,
        ]);
        $subscriptionPlan->categories()->attach($allowedCategory->id);

        $overPricedBook = BookFactory::new()->create([
            'price' => 900,
        ]);
        $this->assertFalse($subscriptionPlan->allowBook($overPricedBook));

        $disallowedCategoryBook = BookFactory::new()->create([
            'price' => 250,
        ]);
        $disallowedCategoryBook->categories()->attach($disallowedCategory->id);
        $this->assertFalse($subscriptionPlan->allowBook($disallowedCategoryBook));

        $book = BookFactory::new()->create([
            'price' => 250,
        ]);
        $book->categories()->attach($allowedCategory->id);
        $this->assertTrue($subscriptionPlan->allowBook($book));
    }
}
