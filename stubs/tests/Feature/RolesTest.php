<?php

use App\Enums\RoleName;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionServiceProvider;

describe('Roles Package', function () {
    it('has permission service provider registered', function () {
        expect(app()->getProviders(PermissionServiceProvider::class))
            ->not->toBeEmpty();
    });

    it('has permission config available', function () {
        expect(config('permission'))->not->toBeNull();
        expect(config('permission.models.role'))->toBe(Role::class);
    });

    it('has role middleware classes available', function () {
        expect(class_exists(\Spatie\Permission\Middleware\RoleMiddleware::class))->toBeTrue();
        expect(class_exists(\Spatie\Permission\Middleware\PermissionMiddleware::class))->toBeTrue();
        expect(class_exists(\Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class))->toBeTrue();
    });
});

describe('RoleName Enum', function () {
    it('has at least one role defined', function () {
        expect(RoleName::cases())->not->toBeEmpty();
    });

    it('has string backed values', function () {
        foreach (RoleName::cases() as $role) {
            expect($role->value)->toBeString();
        }
    });
});

describe('Role Assignment', function () {
    beforeEach(function () {
        // Create roles for testing
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value);
        }
        $this->firstRole = RoleName::cases()[0]->value;
        $this->secondRole = count(RoleName::cases()) > 1 ? RoleName::cases()[1]->value : null;
    });

    it('can create roles from enum', function () {
        foreach (RoleName::cases() as $role) {
            expect(Role::where('name', $role->value)->exists())->toBeTrue();
        }
    });

    it('can assign role to user', function () {
        $user = User::factory()->create();

        $user->assignRole($this->firstRole);

        expect($user->hasRole($this->firstRole))->toBeTrue();
    });

    it('can assign multiple roles to user', function () {
        if (!$this->secondRole) {
            $this->markTestSkipped('Need at least 2 roles to test multiple role assignment');
        }

        $user = User::factory()->create();

        $user->assignRole([$this->firstRole, $this->secondRole]);

        expect($user->hasRole($this->firstRole))->toBeTrue();
        expect($user->hasRole($this->secondRole))->toBeTrue();
    });

    it('can remove role from user', function () {
        $user = User::factory()->create();
        $user->assignRole($this->firstRole);

        expect($user->hasRole($this->firstRole))->toBeTrue();

        $user->removeRole($this->firstRole);

        expect($user->hasRole($this->firstRole))->toBeFalse();
    });

    it('can check user has any role', function () {
        $user = User::factory()->create();
        $user->assignRole($this->firstRole);

        expect($user->hasAnyRole([$this->firstRole]))->toBeTrue();
        expect($user->hasAnyRole(['nonexistent-role']))->toBeFalse();
    });
});
