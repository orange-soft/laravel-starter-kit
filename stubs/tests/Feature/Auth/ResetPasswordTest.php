<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

describe('Reset Password Page', function () {
    it('can view reset password page with valid token', function () {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/ResetPassword')
                ->has('token')
                ->has('email')
            );
    });
});

describe('Password Reset', function () {
    it('can reset password with valid token', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'must_change_password' => true,
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $user->refresh();
        expect(Hash::check('new-password123', $user->password))->toBeTrue();
        expect($user->must_change_password)->toBeFalse();
    });

    it('cannot reset password with invalid token', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->post(route('password.store'), [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertSessionHasErrors('email');

        // Password should remain unchanged
        $user->refresh();
        expect(Hash::check('password', $user->password))->toBeTrue();
    });

    it('cannot reset password with expired token', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Create token and then expire it by traveling in time
        $token = Password::createToken($user);

        // Travel past token expiration (default 60 minutes)
        $this->travel(61)->minutes();

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertSessionHasErrors('email');
    });

    it('validates password confirmation matches', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password123',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('password');
    });

    it('validates required fields', function () {
        $this->post(route('password.store'), [])
            ->assertSessionHasErrors(['token', 'email', 'password']);
    });
});
