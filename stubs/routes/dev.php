<?php

use Illuminate\Support\Facades\Route;

/**
 * Development routes - only available in local environment.
 */
if (app()->environment('local')) {
    Route::get('/dev/typography', fn () => inertia('dev/Typography'))->name('dev.typography');
}
