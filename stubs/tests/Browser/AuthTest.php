<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'must_change_password' => false,
    ]);
});

describe('Login Journey', function () {
    it('can view login page', function () {
        $page = visit(route('login'));

        $page->assertSee('Sign in to your account')
            ->assertSee('Email address')
            ->assertSee('Password')
            ->assertSee('Sign in');
    });

    it('can login with valid credentials', function () {
        $page = visit(route('login'));

        $page->fill('#email', $this->user->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page->assertPathIs('/dashboard')
            ->assertSee('Dashboard');
    });

    it('shows error with invalid credentials', function () {
        $loginPath = parse_url(route('login'), PHP_URL_PATH);
        $page = visit(route('login'));

        $page->fill('#email', $this->user->email)
            ->type('#password input', 'wrong-password')
            ->click('Sign in');

        $page->assertPathIs($loginPath)
            ->assertSee('These credentials do not match our records');
    });

    it('validates required fields', function () {
        $loginPath = parse_url(route('login'), PHP_URL_PATH);
        $page = visit(route('login'));

        $page->click('Sign in');

        $page->assertPathIs($loginPath)
            ->assertSee('The email field is required')
            ->assertSee('The password field is required');
    });
});

describe('Forgot Password Journey', function () {
    it('can view forgot password page', function () {
        $page = visit(route('password.request'));

        $page->assertSee('Reset your password')
            ->assertSee('Email address')
            ->assertSee('Send reset link');
    });

    it('can access forgot password from login page', function () {
        $forgotPasswordPath = parse_url(route('password.request'), PHP_URL_PATH);
        $page = visit(route('login'));

        $page->click('Forgot password?');

        $page->assertPathIs($forgotPasswordPath);
    });
});

describe('Change Password Journey', function () {
    it('redirects to change password when must_change_password is true', function () {
        $changePasswordPath = parse_url(route('password.change'), PHP_URL_PATH);

        // Create user that must change password
        $user = User::factory()->create([
            'email' => 'tempuser@example.com',
            'password' => 'password',
            'must_change_password' => true,
        ]);

        $page = visit(route('login'));

        $page->fill('#email', $user->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page->assertPathIs($changePasswordPath)
            ->assertSee('Change your password');
    });

    it('shows change password form elements', function () {
        $changePasswordPath = parse_url(route('password.change'), PHP_URL_PATH);

        $user = User::factory()->create([
            'email' => 'tempuser@example.com',
            'password' => 'password',
            'must_change_password' => true,
        ]);

        $page = visit(route('login'));

        $page->fill('#email', $user->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page->assertPathIs($changePasswordPath)
            ->assertSee('Current password')
            ->assertSee('New password')
            ->assertSee('Confirm new password')
            ->assertSee('Change password');
    });
});
