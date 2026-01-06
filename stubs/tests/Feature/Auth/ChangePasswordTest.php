<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('Change Password Page', function () {
    it('can view change password page', function () {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get(route('password.change'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/ChangePassword'));
    });

    it('requires authentication', function () {
        $this->get(route('password.change'))
            ->assertRedirect(route('login'));
    });
});

describe('Change Password', function () {
    it('can change password with valid current password', function () {
        $user = User::factory()->create([
            'password' => 'password',
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.change'), [
                'current_password' => 'password',
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])->assertRedirect(route('dashboard'))
                ->assertSessionHas('success', 'Password changed successfully.');

        $user->refresh();
        expect(Hash::check('new-password123', $user->password))->toBeTrue();
        expect($user->must_change_password)->toBeFalse();
    });

    it('cannot change password with incorrect current password', function () {
        $user = User::factory()->create([
            'password' => 'password',
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.change'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])->assertSessionHasErrors('current_password');

        // Password should remain unchanged
        $user->refresh();
        expect(Hash::check('password', $user->password))->toBeTrue();
    });

    it('validates password confirmation matches', function () {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->post(route('password.change'), [
                'current_password' => 'password',
                'password' => 'new-password123',
                'password_confirmation' => 'different-password',
            ])->assertSessionHasErrors('password');
    });

    it('validates required fields', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('password.change'), [])
            ->assertSessionHasErrors(['current_password', 'password']);
    });

    it('requires authentication', function () {
        $this->post(route('password.change'), [
            'current_password' => 'password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect(route('login'));
    });
});
