<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetAccountPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_links_to_forgot_password_and_the_other_ways_in(): void
    {
        $this->get('/en/login')
            ->assertOk()
            ->assertSee('Forgot password?')
            ->assertSee('/en/forgot-password', false)
            ->assertSee('/en/researcher/register', false)
            ->assertSee('/en/submit-idea', false)
            ->assertSee('/admin/login', false);

        $this->get('/login')->assertOk()->assertSee('পাসওয়ার্ড ভুলে গেছেন?', false);
    }

    public function test_a_reset_link_is_emailed(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'someone@example.com']);

        $this->get('/en/forgot-password')->assertOk()->assertSee('Send reset link');

        $this->from('/en/forgot-password')
            ->post('/en/forgot-password', ['email' => 'someone@example.com'])
            ->assertRedirect('/en/forgot-password')
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetAccountPassword::class);
    }

    public function test_an_unknown_email_reads_the_same_and_sends_nothing(): void
    {
        Notification::fake();

        $this->post('/en/forgot-password', ['email' => 'nobody@example.com'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_the_link_sets_a_new_password_and_signs_in(): void
    {
        $user = User::factory()->create(['email' => 'someone@example.com', 'role' => User::RESEARCHER]);
        $token = Password::createToken($user);

        $this->get('/en/reset-password/'.$token.'?email=someone@example.com')
            ->assertOk()
            ->assertSee('someone@example.com');

        $this->post('/en/reset-password', [
            'token' => $token,
            'email' => 'someone@example.com',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));
    }

    public function test_a_wrong_token_is_refused(): void
    {
        $user = User::factory()->create(['email' => 'someone@example.com']);

        $this->from('/en/reset-password/bad')
            ->post('/en/reset-password', [
                'token' => 'bad',
                'email' => 'someone@example.com',
                'password' => 'new-secret-123',
                'password_confirmation' => 'new-secret-123',
            ])
            ->assertRedirect('/en/reset-password/bad')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertFalse(Hash::check('new-secret-123', $user->fresh()->password));
    }
}
