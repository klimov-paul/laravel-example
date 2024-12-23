<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\UserFactory;
use Tests\Support\Payment\BraintreeTrait;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\BraintreeController
 */
class BraintreeTest extends TestCase
{
    use BraintreeTrait;

    protected User $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipOnBraintreeInvalidConfig();

        $this->user = UserFactory::new()->create();
    }

    public function testGenerateClientToken(): void
    {
        $this->actingAs($this->user);

        $this->postJson(route('api.braintree.generate-client-token'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'token',
                ],
            ]);
    }
}
