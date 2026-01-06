<?php

use App\Enums\RoleName;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    foreach (RoleName::cases() as $role) {
        Role::findOrCreate($role->value);
    }

    // Create admin user for testing
    $this->admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'password',
        'must_change_password' => false,
    ]);
    $this->admin->assignRole(RoleName::Admin->value);
});

describe('User Management', function () {
    it('can view users list after login', function () {
        $page = visit(route('login'));

        $page->fill('#email', $this->admin->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page->assertPathIs('/dashboard');

        // Navigate to users via sidebar
        $usersPath = parse_url(route('users.index'), PHP_URL_PATH);
        $page->click("a[href=\"{$usersPath}\"]");

        $page->assertPathIs($usersPath)
            ->assertSee('Users')
            ->assertSee('Add User')
            ->assertSee($this->admin->name);
    });

    it('shows users in table', function () {
        // Create additional users
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $page = visit(route('login'));

        $page->fill('#email', $this->admin->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $usersPath = parse_url(route('users.index'), PHP_URL_PATH);
        $page->click("a[href=\"{$usersPath}\"]");

        $page->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->assertSee($this->admin->name);
    });

    it('can view create user page', function () {
        $page = visit(route('login'));

        $page->fill('#email', $this->admin->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page = visit(route('users.create'));

        $page->assertSee('Add User')
            ->assertSee('Name')
            ->assertSee('Email')
            ->assertSee('Role');
    });

    it('can view edit user page', function () {
        $user = User::factory()->create(['name' => 'Test User']);

        $page = visit(route('login'));

        $page->fill('#email', $this->admin->email)
            ->type('#password input', 'password')
            ->click('Sign in');

        $page = visit(route('users.edit', $user));

        $page->assertSee('Edit Test User')
            ->assertSee('Name')
            ->assertSee('Email')
            ->assertSee('Role')
            ->assertSee('Update User');
    });
});
