<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            \App\Http\Middleware\HandleNavigationContext::class,
        ]);

        $middleware->alias([
            'password.not_temporary' => \App\Http\Middleware\EnsurePasswordIsNotTemporary::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 419 (CSRF token mismatch) by redirecting back with error
        $exceptions->respond(function (Response $response, \Throwable $e, Request $request) {
            if ($response->getStatusCode() === 419) {
                // Try session flash first, fall back to query param if session is broken
                $message = 'Your session has expired. Please try again.';

                if ($request->header('X-Inertia')) {
                    // For Inertia: try back() with errors, fall back to location redirect
                    return back()->withErrors(['session' => $message]);
                }

                return back()->withErrors(['session' => $message]);
            }

            return $response;
        });
    })->create();
