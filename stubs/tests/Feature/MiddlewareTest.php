<?php

use App\Models\User;

describe('EnsurePasswordIsNotTemporary Middleware', function () {
    it('allows access when user does not need to change password', function () {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    });

    it('redirects to change password when must_change_password is true', function () {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('password.change'));
    });

    it('allows access to change password page when must_change_password is true', function () {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get(route('password.change'))
            ->assertOk();
    });

    it('allows access to logout when must_change_password is true', function () {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    });

    it('allows guests to access public routes', function () {
        $this->get(route('login'))
            ->assertOk();
    });
});
