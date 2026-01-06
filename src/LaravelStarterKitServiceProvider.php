<?php

namespace OrangeSoft\LaravelStarterKit;

use Illuminate\Support\ServiceProvider;
use OrangeSoft\LaravelStarterKit\Console\Commands\InstallCommand;

class LaravelStarterKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
