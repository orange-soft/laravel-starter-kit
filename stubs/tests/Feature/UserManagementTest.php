<?php

use App\Enums\RoleName;
use App\Models\User;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles dynamically from enum
    foreach (RoleName::cases() as $role) {
        Role::findOrCreate($role->value);
    }

    // Store role values for use in tests
    $this->firstRole = RoleName::cases()[0]->value;
    $this->secondRole = count(RoleName::cases()) > 1 ? RoleName::cases()[1]->value : $this->firstRole;

    // Create admin user for testing
    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->firstRole);
});

describe('User List', function () {
    it('can view users list', function () {
        User::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/Index')
                ->has('users.data', 4) // 3 + admin
            );
    });

    it('can search users by name', function () {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $this->actingAs($this->admin)
            ->get(route('users.index', ['search' => 'John']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'John Doe')
            );
    });

    it('can search users by email', function () {
        User::factory()->create(['email' => 'john@example.com']);
        User::factory()->create(['email' => 'jane@example.com']);

        $this->actingAs($this->admin)
            ->get(route('users.index', ['search' => 'john@']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 1)
            );
    });

    it('requires authentication', function () {
        $this->get(route('users.index'))
            ->assertRedirect(route('login'));
    });
});

describe('Create User', function () {
    it('can view create user page', function () {
        $this->actingAs($this->admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/Create')
                ->has('roles')
            );
    });

    it('can create a new user', function () {
        Notification::fake();

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'role' => $this->firstRole,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User created successfully.');

        $user = User::where('email', 'newuser@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->name)->toBe('New User');
        expect($user->must_change_password)->toBeTrue();
        expect($user->hasRole($this->firstRole))->toBeTrue();

        Notification::assertSentTo($user, WelcomeNotification::class);
    });

    it('sends welcome notification with temporary password when creating user', function () {
        Notification::fake();

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'Welcome Test User',
                'email' => 'welcometest@example.com',
                'role' => $this->firstRole,
            ]);

        $user = User::where('email', 'welcometest@example.com')->first();

        Notification::assertSentTo($user, WelcomeNotification::class, function ($notification) {
            // Verify the notification contains a temporary password (12 chars)
            return strlen($notification->temporaryPassword) === 12;
        });
    });

    it('sends welcome notification synchronously when queue config is disabled', function () {
        Notification::fake();
        config(['os.notifications.queue' => false]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'Sync Test User',
                'email' => 'synctest@example.com',
                'role' => $this->firstRole,
            ]);

        $user = User::where('email', 'synctest@example.com')->first();

        Notification::assertSentTo($user, WelcomeNotification::class);
    });

    it('validates required fields', function () {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'role']);
    });

    it('validates unique email', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'New User',
                'email' => 'existing@example.com',
                'role' => $this->firstRole,
            ])
            ->assertSessionHasErrors('email');
    });

    it('validates role is valid enum', function () {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'role' => 'invalid-role',
            ])
            ->assertSessionHasErrors('role');
    });

    it('requires authentication', function () {
        $this->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => $this->firstRole,
        ])->assertRedirect(route('login'));
    });
});

describe('Edit User', function () {
    it('can view edit user page', function () {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('users.edit', $user))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/Edit')
                ->has('user')
                ->has('roles')
            );
    });

    it('can update user', function () {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        $user->assignRole($this->firstRole);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'role' => $this->secondRole,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User updated successfully.');

        $user->refresh();
        expect($user->name)->toBe('Updated Name');
        expect($user->email)->toBe('updated@example.com');
        expect($user->hasRole($this->secondRole))->toBeTrue();
    });

    it('validates unique email excludes current user', function () {
        $user = User::factory()->create(['email' => 'user@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        // Should fail - email belongs to another user
        $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'other@example.com',
                'role' => $this->firstRole,
            ])
            ->assertSessionHasErrors('email');

        // Should succeed - keeping own email
        $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'user@example.com',
                'role' => $this->firstRole,
            ])
            ->assertRedirect(route('users.index'));
    });

    it('requires authentication', function () {
        $user = User::factory()->create();

        $this->put(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => $this->firstRole,
        ])->assertRedirect(route('login'));
    });
});

describe('Delete User', function () {
    it('can delete a user', function () {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User deleted successfully.');

        expect(User::find($user->id))->toBeNull();
    });

    it('cannot delete yourself', function () {
        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $this->admin))
            ->assertRedirect()
            ->assertSessionHas('error', 'You cannot delete yourself.');

        expect(User::find($this->admin->id))->not->toBeNull();
    });

    it('requires authentication', function () {
        $user = User::factory()->create();

        $this->delete(route('users.destroy', $user))
            ->assertRedirect(route('login'));
    });
});
