<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\RedirectResponse;

/**
 * RedirectsToStoredIndex Trait
 *
 * Provides controllers with the ability to redirect users back to their stored
 * index page URLs, preserving pagination, filters, and sorting state.
 *
 * Works in conjunction with HandleNavigationContext middleware to provide
 * seamless navigation experience where users return to the exact page and
 * state they were viewing before performing CRUD operations.
 *
 * USAGE:
 * ```php
 * use App\Http\Controllers\Traits\RedirectsToStoredIndex;
 *
 * class UserController extends Controller
 * {
 *     use RedirectsToStoredIndex;
 *
 *     public function store(UserStoreRequest $request)
 *     {
 *         $request->persist();
 *         return $this->redirectToIndex('users.index', 'User created successfully.');
 *     }
 *
 *     public function update(UserUpdateRequest $request, User $user)
 *     {
 *         $request->persist($user);
 *         return $this->redirectToIndex('users.index', 'User updated successfully.');
 *     }
 *
 *     public function destroy(User $user)
 *     {
 *         $user->delete();
 *         return $this->redirectToIndex('users.index', 'User deleted successfully.');
 *     }
 * }
 * ```
 *
 * ERROR MESSAGES:
 * ```php
 * if ($user->id === auth()->id()) {
 *     return $this->redirectToIndex(
 *         'users.index',
 *         'You cannot delete your own account.',
 *         'error'
 *     );
 * }
 * ```
 *
 * @see \App\Http\Middleware\HandleNavigationContext
 */
trait RedirectsToStoredIndex
{
    /**
     * Redirect to the stored index URL or fallback to the given route
     *
     * @param  string  $indexRouteName  The index route name (e.g., 'users.index')
     * @param  string|null  $message  Flash message
     * @param  string  $type  Message type (success, error, warning, info)
     */
    protected function redirectToIndex(
        string $indexRouteName,
        ?string $message = null,
        string $type = 'success'
    ): RedirectResponse {
        // Derive session key from route name
        // e.g., 'users.index' -> 'users_index_url'
        $sessionKey = $this->deriveSessionKey($indexRouteName);

        // Get stored URL from session or fallback to route
        $url = session()->get($sessionKey, route($indexRouteName));

        $redirect = redirect($url);

        if ($message) {
            $redirect->with($type, $message);
        }

        return $redirect;
    }

    /**
     * Derive session key from route name
     */
    private function deriveSessionKey(string $routeName): string
    {
        return str_replace(['.', '-'], '_', $routeName).'_url';
    }
}
