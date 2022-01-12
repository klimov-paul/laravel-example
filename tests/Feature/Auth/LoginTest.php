<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Auth\LoginController
 * @see \App\Http\Controllers\Api\Auth\LoginController
 */
class LoginTest extends TestCase
{
    /**
     * @var \App\Models\User registered user mock
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::new()->create();
    }

    public function testShowLoginForm()
    {
        $response = $this->get(route('auth.login'))
            ->assertSuccessful();

        $content = $response->getContent();

        /*$this->assertMatchesRegularExpression('#<html.*>.+</html>#is', $content);
        $this->assertMatchesRegularExpression('#<head>.+</head>#is', $content);
        $this->assertMatchesRegularExpression('#<body.*>.+</body>#is', $content);*/
    }

    public function testLogin()
    {
        $this->postJson(route('api.auth.login'), [
            'email' => $this->user->email,
            'password' => 'secret',
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

        $this->assertAuthenticated();
    }

    public function testLoginFail()
    {
        $this->postJson(route('api.auth.login'), [
            'email' => 'unexisting-user@unexisting.email',
            'password' => 'secret',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
            ]);

        $this->postJson(route('api.auth.login'), [
            'email' => $this->user->email,
            'password' => 'incorrect-password',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function testLogout()
    {
        $this->actingAs($this->user)
            ->postJson(route('api.auth.logout'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertGuest();
    }

    /**
     * @depends testLogin
     */
    public function testLoginInactiveAccount()
    {
        $this->user->update(['status' => UserStatus::BANNED]);

        $this->postJson(route('api.auth.login'), [
            'email' => $this->user->email,
            'password' => 'secret',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'email',
                ],
            ]);
    }
}
