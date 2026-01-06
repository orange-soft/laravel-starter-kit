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

describe('Profile Page', function () {
    it('can view profile page when authenticated', function () {
        $page = visit(route('login'));

        $page->fill('#email', $this->user->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page->assertPathIs('/dashboard');

        $page = visit(route('profile.edit'));

        $page->assertSee('Profile Information')
            ->assertSee('Update Password')
            ->assertValue('#name', 'Test User')
            ->assertValue('#email', 'test@example.com');
    });

    it('can update profile name', function () {
        $page = visit(route('login'));

        $page->fill('#email', $this->user->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page = visit(route('profile.edit'));

        $page->fill('#name', 'Updated Name')
            ->click('Save');

        $page->assertValue('#name', 'Updated Name');

        expect($this->user->fresh()->name)->toBe('Updated Name');
    });

    it('shows password update form', function () {
        $page = visit(route('login'));

        $page->fill('#email', $this->user->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page = visit(route('profile.edit'));

        $page->assertSee('Update Password')
            ->assertSee('Current Password')
            ->assertSee('New Password')
            ->assertSee('Confirm Password');
    });
});
