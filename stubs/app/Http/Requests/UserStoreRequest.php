<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', Rule::enum(RoleName::class)],
        ];
    }

    public function persist(): User
    {
        $tempPassword = Str::random(12);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $tempPassword,
            'must_change_password' => true,
        ]);

        $user->assignRole($this->role);

        // TODO: Send email with temporary password
        // $user->notify(new WelcomeNotification($tempPassword));

        return $user;
    }
}
