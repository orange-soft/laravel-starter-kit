<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use App\Notifications\Auth\VerifyEmailNotification;

describe('Email Verification Notice', function () {
    it('shows verification notice for unverified users', function () {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/VerifyEmail'));
    });

    it('redirects verified users to dashboard', function () {
        $user = User::factory()->create(); // verified by default

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertRedirect('/dashboard');
    });

    it('requires authentication', function () {
        $this->get(route('verification.notice'))
            ->assertRedirect(route('login'));
    });
});

describe('Email Verification', function () {
    it('can verify email with valid link', function () {
        Event::fake();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect('/dashboard?verified=1');

        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
        Event::assertDispatched(Verified::class);
    });

    it('cannot verify with invalid hash', function () {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertForbidden();

        expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    });

    it('cannot verify with expired link', function () {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(1), // Already expired
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertForbidden();

        expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    });

    it('redirects already verified users', function () {
        $user = User::factory()->create(); // verified by default

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect('/dashboard?verified=1');
    });
});

describe('Resend Verification Email', function () {
    it('can resend verification email', function () {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Verification link sent!');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    });

    it('does not resend if already verified', function () {
        Notification::fake();

        $user = User::factory()->create(); // verified by default

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect('/dashboard');

        Notification::assertNothingSent();
    });

    it('requires authentication', function () {
        $this->post(route('verification.send'))
            ->assertRedirect(route('login'));
    });
});
