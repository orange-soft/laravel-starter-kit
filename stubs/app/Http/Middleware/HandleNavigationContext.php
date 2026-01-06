<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/**
 * Navigation Context Middleware
 *
 * Preserves user navigation state (filters, pagination, sorting) when navigating
 * between index pages and detail/edit/create views. Runs globally on all web
 * routes and auto-detects resource route patterns.
 *
 * PROBLEM IT SOLVES:
 * - User is on page 7 of users list with search filters applied
 * - User clicks "Edit" on a user
 * - After saving, user is redirected to page 1 with no filters
 * - User loses their context and has to navigate back manually
 *
 * WITH THIS MIDDLEWARE:
 * - User is returned to the exact same page with all filters intact
 * - Navigation feels natural and preserves user workflow
 *
 * HOW IT WORKS:
 * 1. On *.index routes: Stores full URL (with query params) in session
 * 2. On *.create, *.edit, *.show routes: Makes stored URL available to views
 * 3. Controllers use RedirectsToStoredIndex trait to redirect back
 *
 * AUTO-DETECTION:
 * - Route: users.index     → Session key: users_index_url
 * - Route: users.edit      → Derives index: users.index
 * - Route: posts.create    → Derives index: posts.index
 *
 * USAGE:
 * This middleware runs globally (registered in bootstrap/app.php).
 * In controllers, use the RedirectsToStoredIndex trait:
 *
 * ```php
 * use App\Http\Controllers\Traits\RedirectsToStoredIndex;
 *
 * class UserController extends Controller
 * {
 *     use RedirectsToStoredIndex;
 *
 *     public function store(UserStoreRequest $request): RedirectResponse
 *     {
 *         $request->persist();
 *         return $this->redirectToIndex('users.index', 'User created.');
 *     }
 *
 *     public function update(UserUpdateRequest $request, User $user): RedirectResponse
 *     {
 *         $request->persist($user);
 *         return $this->redirectToIndex('users.index', 'User updated.');
 *     }
 *
 *     public function destroy(User $user): RedirectResponse
 *     {
 *         $user->delete();
 *         return $this->redirectToIndex('users.index', 'User deleted.');
 *     }
 * }
 * ```
 *
 * @see \App\Http\Controllers\Traits\RedirectsToStoredIndex
 */
class HandleNavigationContext
{
    /**
     * Handle an incoming request.
     *
     * @param string|null $sessionKey Custom session key (auto-derived if not provided)
     * @param string|null $indexRoute Custom index route name (auto-derived if not provided)
     */
    public function handle(Request $request, Closure $next, ?string $sessionKey = null, ?string $indexRoute = null): mixed
    {
        $routeName = $request->route()->getName();

        if (!$routeName) {
            return $next($request);
        }

        // Auto-derive parameters if not provided
        if (empty($sessionKey) || empty($indexRoute)) {
            $indexRoute = $this->deriveIndexRoute($routeName);
            $sessionKey = $this->deriveSessionKey($indexRoute);
        }

        // Handle different actions
        if ($this->isIndexRoute($routeName)) {
            $this->handleIndexRoute($request, $sessionKey);
        } elseif ($this->isNavigableRoute($routeName)) {
            $this->handleNavigableRoute($request, $sessionKey, $indexRoute);
        }

        return $next($request);
    }

    /**
     * Derive index route from current route
     * Example: 'users.edit' -> 'users.index'
     */
    private function deriveIndexRoute(string $routeName): string
    {
        $baseName = preg_replace('/\.(create|store|show|edit|update|destroy)$/', '', $routeName);

        return $baseName.'.index';
    }

    /**
     * Derive session key from index route name
     * Example: 'users.index' -> 'users_index_url'
     */
    private function deriveSessionKey(string $indexRouteName): string
    {
        return str_replace(['.', '-'], '_', $indexRouteName).'_url';
    }

    /**
     * Check if route is an index route
     */
    private function isIndexRoute(string $routeName): bool
    {
        return str_ends_with($routeName, '.index');
    }

    /**
     * Check if route needs navigation context (show, edit, create)
     */
    private function isNavigableRoute(string $routeName): bool
    {
        return str_ends_with($routeName, '.show')
            || str_ends_with($routeName, '.edit')
            || str_ends_with($routeName, '.create');
    }

    /**
     * Handle index route - store current URL
     */
    private function handleIndexRoute(Request $request, string $sessionKey): void
    {
        session()->put($sessionKey, $request->fullUrl());
    }

    /**
     * Handle navigable routes - provide stored URL and store from referrer
     */
    private function handleNavigableRoute(Request $request, string $sessionKey, string $indexRoute): void
    {
        // Store URL from referrer if it's an index page with filters
        $this->storeIndexUrlFromReferrer($request, $sessionKey, $indexRoute);

        // Make indexUrl available to all views
        $indexUrl = $this->getStoredIndexUrl($sessionKey, $indexRoute);
        View::share('indexUrl', $indexUrl);
    }

    /**
     * Store index URL from referrer if it contains the index route
     */
    private function storeIndexUrlFromReferrer(Request $request, string $sessionKey, string $indexRouteName): void
    {
        $referrer = $request->header('referer');

        if (!$referrer || !str_contains($referrer, '?')) {
            return;
        }

        try {
            $expectedUrl = route($indexRouteName);
            $parsedReferrer = parse_url($referrer);
            $parsedExpected = parse_url($expectedUrl);

            // Validate referrer is from same domain and matches expected route
            if ($parsedReferrer && $parsedExpected &&
                isset($parsedReferrer['host'], $parsedExpected['host']) &&
                $parsedReferrer['host'] === $parsedExpected['host'] &&
                isset($parsedReferrer['path'], $parsedExpected['path']) &&
                str_starts_with($parsedReferrer['path'], $parsedExpected['path'])) {
                session()->put($sessionKey, $referrer);
            }
        } catch (\Exception $e) {
            // Silently fail if route doesn't exist or URL parsing fails
        }
    }

    /**
     * Get the stored index URL from session
     */
    private function getStoredIndexUrl(string $sessionKey, string $fallbackRoute): string
    {
        $storedUrl = session()->get($sessionKey);

        if ($storedUrl) {
            return $storedUrl;
        }

        try {
            return Route::has($fallbackRoute)
                ? route($fallbackRoute)
                : (url()->previous() ?: '/');
        } catch (\Exception $e) {
            return url()->previous() ?: '/';
        }
    }
}
