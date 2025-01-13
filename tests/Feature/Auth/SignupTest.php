<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\UserSignedUp;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Auth\SignupController
 * @see \App\Http\Controllers\Api\Auth\SignupController
 */
class SignupTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function testShowSignupForm(): void
    {
        $response = $this->get(route('auth.signup'))
            ->assertSuccessful();

        $content = $response->getContent();

        /*$this->assertMatchesRegularExpression('#<html.*>.+</html>#is', $content);
        $this->assertMatchesRegularExpression('#<head>.+</head>#is', $content);
        $this->assertMatchesRegularExpression('#<body.*>.+</body>#is', $content);*/
    }

    public function testSignup(): void
    {
        $user = UserFactory::new()->make();

        $this->postJson(route('api.auth.signup'), [
            'name' => $user->name,
            'email' => $user->email,
            //'verification_token' => RecaptchaRule::VALID_TEST_TOKEN,
        ])
            ->assertSuccessful()
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertGuest();

        $createdUser = User::query()->where('email', $user->email)->first();

        $this->assertNotEmpty($createdUser);

        $verificationUrl = null;

        Notification::assertSentTo(
            $createdUser,
            UserSignedUp::class,
            function (UserSignedUp $notification, $channels) use ($createdUser, &$verificationUrl) {
                $verificationUrl = $notification->toMail($createdUser)->actionUrl;

                return ! empty($notification->password);
            }
        );

        $this->assertNotEmpty($verificationUrl);

        $this->getJson($verificationUrl)
            ->assertRedirect();

        $this->assertAuthenticatedAs($createdUser);
    }
}
