<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $currentUser = $this->user();
        $targetUser = $this->route('user');

        if (! $currentUser->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return false;
        }

        // Only super-admins can edit super-admins
        if ($targetUser->hasRole(RoleName::SuperAdmin->value) && ! $currentUser->hasRole(RoleName::SuperAdmin->value)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'role' => ['required', Rule::enum(RoleName::class)],
        ];
    }

    public function persist(User $user): User
    {
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $user->syncRoles([$this->role]);

        return $user;
    }
}
