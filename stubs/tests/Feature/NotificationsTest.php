<?php

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
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
