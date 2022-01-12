<?php

namespace Tests\Feature\Auth;

use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\Auth\ForgotPasswordController
 * @see \App\Http\Controllers\Api\Auth\ResetPasswordController
 */
class ForgotPasswordTest extends TestCase
{
    /**
     * @var \App\Models\User registered user mock
     */
    protected $user;

    /**
     * @var \Illuminate\Auth\Passwords\PasswordBroker user password broker
     */
    protected $passwordBroker;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->passwordBroker = Password::broker('users');

        $this->user = UserFactory::new()->create();
    }

    public function testShowLinkRequestForm()
    {
        $response = $this->get(route('auth.password.request'))
            ->assertSuccessful();

        $content = $response->getContent();

        /*$this->assertMatchesRegularExpression('#<html.*>.+</html>#is', $content);
        $this->assertMatchesRegularExpression('#<head>.+</head>#is', $content);
        $this->assertMatchesRegularExpression('#<body.*>.+</body>#is', $content);*/
    }

    public function testSendResetLink()
    {
        $this->postJson(route('api.auth.password.email'), [
            'email' => $this->user->email,
            //'verification_token' => RecaptchaRule::VALID_TEST_TOKEN,
        ])
            ->assertSuccessful()
            ->assertJsonStructure([
                'message',
            ]);

        $passwordResetUrl = null;

        Notification::assertSentTo(
            $this->user,
            ResetPassword::class,
            function (ResetPassword $notification, $channels) use (&$passwordResetUrl) {
                $passwordResetUrl = $notification->toMail($this->user)->actionUrl;

                return ! empty($notification->token);
            }
        );

        $this->assertStringContainsString(urlencode($this->user->email), $passwordResetUrl);

        $this->getJson($passwordResetUrl)
            ->assertSuccessful();
    }

    /**
     * @depends testSendResetLink
     */
    public function testResetPassword()
    {
        $token = $this->passwordBroker->createToken($this->user);

        $newPassword = 'new-password-2';

        $this->postJson(route('api.auth.password.reset'), [
            'email' => $this->user->email,
            'token' => $token,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ])
            ->assertSuccessful()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ],
                ],
            ]);

        $this->assertCredentials([
            'email' => $this->user->email,
            'password' => $newPassword,
        ]);

        $this->assertAuthenticated();
    }

    /**
     * @depends testSendResetLink
     */
    public function testResetPasswordFail()
    {
        $newPassword = 'new-password-2';

        $this->postJson(route('api.auth.password.reset'), [
            'email' => $this->user->email,
            'token' => 'invalid-token',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertGuest();
    }
}
