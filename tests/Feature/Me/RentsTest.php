<?php

namespace Tests\Feature\Me;

use App\Models\User;
use Database\Factories\BookFactory;
use Database\Factories\SubscriptionPlanFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\Me\RentController
 */
class RentsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::new()->create();
    }

    public function testStore()
    {
        $this->actingAs($this->user);

        $book = BookFactory::new()->create();

        $subscriptionPlan = SubscriptionPlanFactory::new()->create([
            'max_book_price' => $book->price + 1,
        ]);
        $subscriptionPlan->subscribe($this->user);

        $this->postJson(route('api.me.rents.store'), ['book_id' => $book->id])
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'created_at',
                    'book' => [
                        'id',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'book' => [
                        'id' => $book->id,
                    ],
                ],
            ]);

        $this->assertTrue($this->user->rents()->where('book_id', $book->id)->exists());
    }
}
