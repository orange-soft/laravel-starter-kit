<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChangePasswordController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/ChangePassword');
    }

    public function store(ChangePasswordRequest $request): RedirectResponse
    {
        $request->persist();

        return redirect()->route('dashboard')->with('success', 'Password changed successfully.');
    }
}
