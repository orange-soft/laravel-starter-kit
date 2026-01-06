<?php

use App\Models\User;

describe('Login Page', function () {
    it('can view login page', function () {
        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/Login'));
    });

    it('redirects authenticated users to dashboard', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect('/dashboard');
    });
});

describe('Login Authentication', function () {
    it('can login with valid credentials', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
            'must_change_password' => false,
        ]);

        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    });

    it('cannot login with invalid password', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    it('cannot login with non-existent email', function () {
        $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    it('validates required fields', function () {
        $this->post(route('login'), [])
            ->assertSessionHasErrors(['email', 'password']);
    });

    it('validates email format', function () {
        $this->post(route('login'), [
            'email' => 'not-an-email',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    });
});

describe('Login with Must Change Password', function () {
    it('redirects to change password page when must_change_password is true', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
            'must_change_password' => true,
        ]);

        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertRedirect(route('password.change'));

        $this->assertAuthenticatedAs($user);
    });
});

describe('Login Rate Limiting', function () {
    it('locks out after too many failed attempts', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Attempt to login 6 times with wrong password
        for ($i = 0; $i < 6; $i++) {
            $this->post(route('login'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // The 6th attempt should show throttle message
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        expect($response->getSession()->get('errors')->get('email')[0])
            ->toContain('Too many login attempts');
    });
});
