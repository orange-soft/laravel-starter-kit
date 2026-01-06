<?php

use App\Models\User;

describe('Logout', function () {
    it('can logout authenticated user', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    });

    it('redirects unauthenticated users', function () {
        $this->post(route('logout'))
            ->assertRedirect(route('login'));
    });
});
