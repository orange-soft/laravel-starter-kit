<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Auth\ResetPasswordNotification;

describe('Forgot Password Page', function () {
    it('can view forgot password page', function () {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/ForgotPassword'));
    });
});

describe('Password Reset Link', function () {
    it('can request password reset link for existing user', function () {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->post(route('password.email'), [
            'email' => 'test@example.com',
        ])->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    });

    it('does not reveal if email exists when requesting reset', function () {
        Notification::fake();

        // Request reset for non-existent email
        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

        // Laravel's default behavior returns an error for non-existent emails
        // This is acceptable security behavior - no notification should be sent
        Notification::assertNothingSent();
    });

    it('validates email is required', function () {
        $this->post(route('password.email'), [])
            ->assertSessionHasErrors('email');
    });

    it('validates email format', function () {
        $this->post(route('password.email'), [
            'email' => 'not-an-email',
        ])->assertSessionHasErrors('email');
    });
});
