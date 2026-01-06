<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('Profile Page', function () {
    it('can view profile page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('profile/Edit')
                ->has('user')
            );
    });

    it('requires authentication', function () {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    });
});

describe('Profile Update', function () {
    it('can update name', function () {
        $user = User::factory()->create([
            'name' => 'Original Name',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success', 'Profile updated successfully.');

        expect($user->fresh()->name)->toBe('Updated Name');
    });

    it('can update email', function () {
        $user = User::factory()->create([
            'email' => 'original@example.com',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'updated@example.com',
            ])
            ->assertRedirect(route('profile.edit'));

        expect($user->fresh()->email)->toBe('updated@example.com');
    });

    it('cannot use email that belongs to another user', function () {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'existing@example.com',
            ])
            ->assertSessionHasErrors('email');

        expect($user->fresh()->email)->not->toBe('existing@example.com');
    });

    it('validates required fields', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [])
            ->assertSessionHasErrors(['name', 'email']);
    });

    it('validates email format', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'not-an-email',
            ])
            ->assertSessionHasErrors('email');
    });

    it('requires authentication', function () {
        $this->put(route('profile.update'), [
            'name' => 'Test',
            'email' => 'test@example.com',
        ])->assertRedirect(route('login'));
    });
});

describe('Password Update from Profile', function () {
    it('can update password', function () {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertRedirect(route('profile.edit'));

        expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
    });

    it('cannot update password with incorrect current password', function () {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'wrong-password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertSessionHasErrors('current_password');

        // Password should remain unchanged
        expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
    });

    it('requires current password when updating password', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertSessionHasErrors('current_password');
    });

    it('validates password confirmation matches', function () {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'different-password',
            ])
            ->assertSessionHasErrors('password');
    });

    it('can update profile without changing password', function () {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->name)->toBe('Updated Name');
        expect(Hash::check('password', $user->password))->toBeTrue();
    });
});
