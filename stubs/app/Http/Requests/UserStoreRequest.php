<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use App\Models\User;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value]);
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
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        $user->assignRole($this->role);

        $notification = new WelcomeNotification($tempPassword);

        if (config('os.notifications.queue', false)) {
            $user->notify($notification);
        } else {
            $user->notifyNow($notification);
        }

        return $user;
    }
}
