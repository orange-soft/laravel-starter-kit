<?php

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Support\Facades\Notification;

describe('Reset Password Notification', function () {
    it('can be sent to user', function () {
        Notification::fake();

        $user = User::factory()->create();
        $token = 'test-token';

        $user->notify(new ResetPasswordNotification($token));

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    });

    it('contains reset password link', function () {
        $user = User::factory()->create();
        $token = 'test-token';

        $notification = new ResetPasswordNotification($token);
        $mailMessage = $notification->toMail($user);

        expect($mailMessage->actionUrl)->toContain('reset-password');
        expect($mailMessage->actionUrl)->toContain($token);
    });

    it('is queued by default', function () {
        $notification = new ResetPasswordNotification('test-token');

        expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });
});

describe('Verify Email Notification', function () {
    it('can be sent to user', function () {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->notify(new VerifyEmailNotification);

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    });

    it('contains verification link', function () {
        $user = User::factory()->unverified()->create();

        $notification = new VerifyEmailNotification;
        $mailMessage = $notification->toMail($user);

        expect($mailMessage->actionUrl)->toContain('verify-email');
        expect($mailMessage->actionUrl)->toContain((string) $user->id);
    });

    it('is queued by default', function () {
        $notification = new VerifyEmailNotification;

        expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });
});

describe('User Notification Methods', function () {
    it('sends custom reset password notification', function () {
        Notification::fake();

        $user = User::factory()->create();

        $user->sendPasswordResetNotification('test-token');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    });

    it('sends custom email verification notification', function () {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    });
});

describe('Welcome Notification', function () {
    it('can be sent to user', function () {
        Notification::fake();

        $user = User::factory()->create();
        $tempPassword = 'temp-password-123';

        $user->notify(new WelcomeNotification($tempPassword));

        Notification::assertSentTo($user, WelcomeNotification::class);
    });

    it('contains temporary password', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $tempPassword = 'temp-password-123';

        $notification = new WelcomeNotification($tempPassword);
        $mailMessage = $notification->toMail($user);

        expect($mailMessage->introLines)->toContain('Your temporary password is: **temp-password-123**');
    });

    it('contains login link', function () {
        $user = User::factory()->create();
        $tempPassword = 'temp-password-123';

        $notification = new WelcomeNotification($tempPassword);
        $mailMessage = $notification->toMail($user);

        expect($mailMessage->actionUrl)->toContain('login');
    });

    it('greets user by name', function () {
        $user = User::factory()->create(['name' => 'Jane Smith']);
        $tempPassword = 'temp-password-123';

        $notification = new WelcomeNotification($tempPassword);
        $mailMessage = $notification->toMail($user);

        expect($mailMessage->greeting)->toBe('Hello Jane Smith!');
    });

    it('has correct subject', function () {
        $user = User::factory()->create();
        $tempPassword = 'temp-password-123';

        $notification = new WelcomeNotification($tempPassword);
        $mailMessage = $notification->toMail($user);

        expect($mailMessage->subject)->toContain('Welcome to');
    });

    it('is queued by default', function () {
        $notification = new WelcomeNotification('temp-password-123');

        expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });

    it('informs user about password change requirement', function () {
        $user = User::factory()->create();
        $tempPassword = 'temp-password-123';

        $notification = new WelcomeNotification($tempPassword);
        $mailMessage = $notification->toMail($user);

        expect($mailMessage->introLines)->toContain('You will be required to change your password upon first login.');
    });
});
