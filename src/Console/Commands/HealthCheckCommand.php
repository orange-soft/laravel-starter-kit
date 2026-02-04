<?php

namespace OrangeSoft\LaravelStarterKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HealthCheckCommand extends Command
{
    protected $signature = 'os:starter:check';

    protected $description = 'Check the health of your OrangeSoft Laravel Starter Kit integration';

    protected int $passCount = 0;

    protected int $failCount = 0;

    protected int $warnCount = 0;

    public function handle(): int
    {
        $this->components->info('OrangeSoft Laravel Starter Kit Health Check');
        $this->newLine();

        $this->checkUserModel();
        $this->checkMiddleware();
        $this->checkDatabase();
        $this->checkConfig();
        $this->checkRoutes();
        $this->checkRequiredFiles();
        $this->checkRoleEnum();
        $this->checkComposerPackages();
        $this->checkNodePackages();
        $this->checkTestConfiguration();

        $this->newLine();
        $this->showSummary();

        return $this->failCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function checkUserModel(): void
    {
        $this->components->twoColumnDetail('<fg=blue>User Model</>', '');

        if (! class_exists(\App\Models\User::class)) {
            $this->checkFail('User model not found');

            return;
        }

        $userClass = \App\Models\User::class;
        $traits = class_uses_recursive($userClass);
        $hasMissingTraits = false;
        $missingInterface = false;

        // Check for MustVerifyEmail interface
        if (is_subclass_of($userClass, \Illuminate\Contracts\Auth\MustVerifyEmail::class)) {
            $this->checkPass('MustVerifyEmail interface');
        } else {
            $this->checkFail('MustVerifyEmail interface missing');
            $missingInterface = true;
        }

        // Check for HasUuidRouteKey trait
        if (isset($traits[\App\Models\Traits\HasUuidRouteKey::class])) {
            $this->checkPass('HasUuidRouteKey trait');
        } else {
            $this->checkFail('HasUuidRouteKey trait missing');
            $hasMissingTraits = true;
        }

        // Check for HasTemporaryPassword trait
        if (isset($traits[\App\Models\Traits\HasTemporaryPassword::class])) {
            $this->checkPass('HasTemporaryPassword trait');
        } else {
            $this->checkFail('HasTemporaryPassword trait missing');
            $hasMissingTraits = true;
        }

        // Check for HasRoles trait (Spatie)
        if (isset($traits[\Spatie\Permission\Traits\HasRoles::class])) {
            $this->checkPass('HasRoles trait (Spatie)');
        } else {
            $this->checkFail('HasRoles trait missing');
            $hasMissingTraits = true;
        }

        // Check for SoftDeletes trait
        if (isset($traits[\Illuminate\Database\Eloquent\SoftDeletes::class])) {
            $this->checkPass('SoftDeletes trait');
        } else {
            $this->checkFail('SoftDeletes trait missing');
            $hasMissingTraits = true;
        }

        // Show code snippet if any traits or interface are missing
        if ($hasMissingTraits || $missingInterface) {
            $this->newLine();
            $this->line('  <fg=yellow>Add these imports to app/Models/User.php:</>');
            $this->line('  <fg=gray>use App\Models\Traits\HasTemporaryPassword;</>');
            $this->line('  <fg=gray>use App\Models\Traits\HasUuidRouteKey;</>');
            $this->line('  <fg=gray>use Illuminate\Contracts\Auth\MustVerifyEmail;</>');
            $this->line('  <fg=gray>use Illuminate\Database\Eloquent\SoftDeletes;</>');
            $this->line('  <fg=gray>use Spatie\Permission\Traits\HasRoles;</>');
            $this->newLine();
            $this->line('  <fg=yellow>Implement interface and add traits:</>');
            $this->line('  <fg=gray>class User extends Authenticatable implements MustVerifyEmail</>');
            $this->line('  <fg=gray>{</>');
            $this->line('  <fg=gray>    use HasRoles, HasTemporaryPassword, HasUuidRouteKey, SoftDeletes;</>');
            $this->newLine();
        }

        // Check for notification method overrides
        $hasMissingMethods = false;

        if (method_exists($userClass, 'sendPasswordResetNotification')) {
            // Check if it's overridden (not just inherited)
            $reflection = new \ReflectionMethod($userClass, 'sendPasswordResetNotification');
            if ($reflection->getDeclaringClass()->getName() === $userClass) {
                $this->checkPass('sendPasswordResetNotification method');
            } else {
                $this->checkFail('sendPasswordResetNotification method not overridden');
                $hasMissingMethods = true;
            }
        } else {
            $this->checkFail('sendPasswordResetNotification method missing');
            $hasMissingMethods = true;
        }

        if (method_exists($userClass, 'sendEmailVerificationNotification')) {
            // Check if it's overridden (not just inherited)
            $reflection = new \ReflectionMethod($userClass, 'sendEmailVerificationNotification');
            if ($reflection->getDeclaringClass()->getName() === $userClass) {
                $this->checkPass('sendEmailVerificationNotification method');
            } else {
                $this->checkFail('sendEmailVerificationNotification method not overridden');
                $hasMissingMethods = true;
            }
        } else {
            $this->checkFail('sendEmailVerificationNotification method missing');
            $hasMissingMethods = true;
        }

        if ($hasMissingMethods) {
            $this->newLine();
            $this->line('  <fg=yellow>Add notification imports:</>');
            $this->line('  <fg=gray>use App\Notifications\Auth\ResetPasswordNotification;</>');
            $this->line('  <fg=gray>use App\Notifications\Auth\VerifyEmailNotification;</>');
            $this->newLine();
            $this->line('  <fg=yellow>Add these methods to use custom notifications:</>');
            $this->line('  <fg=gray>public function sendPasswordResetNotification($token): void</>');
            $this->line('  <fg=gray>{</>');
            $this->line('  <fg=gray>    $this->notify(new ResetPasswordNotification($token));</>');
            $this->line('  <fg=gray>}</>');
            $this->newLine();
            $this->line('  <fg=gray>public function sendEmailVerificationNotification(): void</>');
            $this->line('  <fg=gray>{</>');
            $this->line('  <fg=gray>    $this->notify(new VerifyEmailNotification);</>');
            $this->line('  <fg=gray>}</>');
            $this->newLine();
        }
    }

    protected function checkMiddleware(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Middleware</>', '');

        $bootstrapPath = base_path('bootstrap/app.php');

        if (! file_exists($bootstrapPath)) {
            $this->checkFail('bootstrap/app.php not found');

            return;
        }

        $content = file_get_contents($bootstrapPath);
        $hasMissingMiddleware = false;

        // Check HandleInertiaRequests
        if (str_contains($content, 'HandleInertiaRequests')) {
            $this->checkPass('HandleInertiaRequests middleware');
        } else {
            $this->checkFail('HandleInertiaRequests middleware missing');
            $hasMissingMiddleware = true;
        }

        // Check HandleNavigationContext
        if (str_contains($content, 'HandleNavigationContext')) {
            $this->checkPass('HandleNavigationContext middleware');
        } else {
            $this->checkFail('HandleNavigationContext middleware missing');
            $hasMissingMiddleware = true;
        }

        // Check password.not_temporary alias
        if (str_contains($content, 'password.not_temporary')) {
            $this->checkPass('password.not_temporary middleware alias');
        } else {
            $this->checkFail('password.not_temporary middleware alias missing');
            $hasMissingMiddleware = true;
        }

        // Check Spatie permission middleware
        if (str_contains($content, 'RoleMiddleware') || str_contains($content, "'role'")) {
            $this->checkPass('Spatie role middleware');
        } else {
            $this->checkFail('Spatie role middleware missing');
            $hasMissingMiddleware = true;
        }

        // Show code snippet if any middleware is missing
        if ($hasMissingMiddleware) {
            $this->newLine();
            $this->line('  <fg=yellow>Add to withMiddleware() in bootstrap/app.php:</>');
            $this->line('  <fg=gray>$middleware->web(append: [</>');
            $this->line('  <fg=gray>    \App\Http\Middleware\HandleInertiaRequests::class,</>');
            $this->line('  <fg=gray>    \App\Http\Middleware\HandleNavigationContext::class,</>');
            $this->line('  <fg=gray>]);</>');
            $this->line('  <fg=gray>$middleware->alias([</>');
            $this->line('  <fg=gray>    \'password.not_temporary\' => \App\Http\Middleware\EnsurePasswordIsNotTemporary::class,</>');
            $this->line('  <fg=gray>    \'role\' => \Spatie\Permission\Middleware\RoleMiddleware::class,</>');
            $this->line('  <fg=gray>    \'permission\' => \Spatie\Permission\Middleware\PermissionMiddleware::class,</>');
            $this->line('  <fg=gray>    \'role_or_permission\' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,</>');
            $this->line('  <fg=gray>]);</>');
            $this->newLine();
        }
    }

    protected function checkDatabase(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Database</>', '');

        try {
            // Check if we can connect
            DB::connection()->getPdo();

            // Check users table columns
            if (Schema::hasTable('users')) {
                if (Schema::hasColumn('users', 'uuid')) {
                    $this->checkPass('users.uuid column');
                } else {
                    $this->checkFail('users.uuid column missing', 'Run: php artisan migrate');
                }

                if (Schema::hasColumn('users', 'must_change_password')) {
                    $this->checkPass('users.must_change_password column');
                } else {
                    $this->checkFail('users.must_change_password column missing', 'Run: php artisan migrate');
                }

                if (Schema::hasColumn('users', 'deleted_at')) {
                    $this->checkPass('users.deleted_at column (soft deletes)');
                } else {
                    $this->checkWarn('users.deleted_at column missing (soft deletes)');
                }
            } else {
                $this->checkFail('users table not found', 'Run: php artisan migrate');
            }

            // Check roles table (Spatie)
            if (Schema::hasTable('roles')) {
                $this->checkPass('roles table (Spatie)');
            } else {
                $this->checkFail('roles table missing', 'Run: php artisan migrate');
            }

            // Check permissions table (Spatie)
            if (Schema::hasTable('permissions')) {
                $this->checkPass('permissions table (Spatie)');
            } else {
                $this->checkFail('permissions table missing', 'Run: php artisan migrate');
            }

            // Check if roles are seeded
            if (Schema::hasTable('roles') && DB::table('roles')->count() > 0) {
                $this->checkPass('Roles seeded');
            } else {
                $this->checkWarn('No roles found', 'Run: php artisan db:seed --class=RoleSeeder');
            }
        } catch (\Exception $e) {
            $this->checkWarn('Database connection failed: ' . $e->getMessage());
        }
    }

    protected function checkConfig(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Configuration</>', '');

        // Check os.php config
        if (file_exists(config_path('os.php'))) {
            $this->checkPass('config/os.php');
        } else {
            $this->checkFail('config/os.php missing');
        }

        // Check permission.php config (Spatie)
        if (file_exists(config_path('permission.php'))) {
            $this->checkPass('config/permission.php (Spatie)');
        } else {
            $this->checkFail('config/permission.php missing', 'Run: php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"');
        }

        // Check DEFAULT_USER_PASSWORD env
        $defaultPassword = config('os.default_user.password');
        if (! empty($defaultPassword)) {
            $this->checkPass('DEFAULT_USER_PASSWORD configured');
        } else {
            $this->checkWarn('DEFAULT_USER_PASSWORD not set in .env');
        }
    }

    protected function checkRoutes(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Routes</>', '');

        // Check auth routes
        if (file_exists(base_path('routes/auth.php'))) {
            $this->checkPass('routes/auth.php');
        } else {
            $this->checkFail('routes/auth.php missing');
        }

        // Check admin routes
        if (file_exists(base_path('routes/admin.php'))) {
            $this->checkPass('routes/admin.php');
        } else {
            $this->checkFail('routes/admin.php missing');
        }

        // Check web.php includes
        $webRoutes = file_exists(base_path('routes/web.php'))
            ? file_get_contents(base_path('routes/web.php'))
            : '';

        if (str_contains($webRoutes, "require __DIR__.'/auth.php'")) {
            $this->checkPass('auth.php included in web.php');
        } else {
            $this->checkFail('auth.php not included in web.php');
        }

        if (str_contains($webRoutes, "require __DIR__.'/admin.php'")) {
            $this->checkPass('admin.php included in web.php');
        } else {
            $this->checkFail('admin.php not included in web.php');
        }
    }

    protected function checkRequiredFiles(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Required Files</>', '');

        $requiredFiles = [
            'Traits' => [
                app_path('Models/Traits/HasUuidRouteKey.php'),
                app_path('Models/Traits/HasTemporaryPassword.php'),
            ],
            'Middleware' => [
                app_path('Http/Middleware/HandleInertiaRequests.php'),
                app_path('Http/Middleware/HandleNavigationContext.php'),
                app_path('Http/Middleware/EnsurePasswordIsNotTemporary.php'),
            ],
            'Notifications' => [
                app_path('Notifications/Auth/ResetPasswordNotification.php'),
                app_path('Notifications/Auth/VerifyEmailNotification.php'),
                app_path('Notifications/Auth/WelcomeNotification.php'),
            ],
            'Seeders' => [
                database_path('seeders/RoleSeeder.php'),
                database_path('seeders/AdminUserSeeder.php'),
            ],
        ];

        foreach ($requiredFiles as $category => $files) {
            foreach ($files as $file) {
                $filename = basename($file);
                if (file_exists($file)) {
                    $this->checkPass("{$filename}");
                } else {
                    $this->checkFail("{$filename} missing");
                }
            }
        }
    }

    protected function checkRoleEnum(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Role Configuration</>', '');

        $roleNamePath = app_path('Enums/RoleName.php');

        if (! file_exists($roleNamePath)) {
            $this->checkFail('RoleName.php not found');

            return;
        }

        $content = file_get_contents($roleNamePath);
        $hasMissingRoles = false;

        // Check for SuperAdmin role (case SuperAdmin = 'super-admin')
        if (preg_match('/case\s+SuperAdmin\s*=/', $content)) {
            $this->checkPass('SuperAdmin role defined');
        } else {
            $this->checkFail('SuperAdmin role missing');
            $hasMissingRoles = true;
        }

        // Check for Admin role (case Admin = 'admin')
        if (preg_match('/case\s+Admin\s*=/', $content)) {
            $this->checkPass('Admin role defined');
        } else {
            $this->checkFail('Admin role missing');
            $hasMissingRoles = true;
        }

        // Show code snippet if any roles are missing
        if ($hasMissingRoles) {
            $this->newLine();
            $this->line('  <fg=yellow>Required roles in app/Enums/RoleName.php:</>');
            $this->line('  <fg=gray>case SuperAdmin = \'super-admin\';</>');
            $this->line('  <fg=gray>case Admin = \'admin\';</>');
            $this->newLine();
            $this->line('  <fg=yellow>You may add additional custom roles as needed.</>');
            $this->newLine();
        }
    }

    protected function checkComposerPackages(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Composer Packages</>', '');

        $composerLock = base_path('composer.lock');

        if (! file_exists($composerLock)) {
            $this->checkWarn('composer.lock not found');

            return;
        }

        $lockContent = json_decode(file_get_contents($composerLock), true);
        $packages = collect($lockContent['packages'] ?? [])
            ->pluck('name')
            ->merge(collect($lockContent['packages-dev'] ?? [])->pluck('name'))
            ->toArray();

        $requiredPackages = [
            'inertiajs/inertia-laravel',
            'spatie/laravel-permission',
        ];

        foreach ($requiredPackages as $package) {
            if (in_array($package, $packages)) {
                $this->checkPass($package);
            } else {
                $this->checkFail("{$package} not installed");
            }
        }
    }

    protected function checkNodePackages(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Node Packages</>', '');

        $packageJson = base_path('package.json');

        if (! file_exists($packageJson)) {
            $this->checkWarn('package.json not found');

            return;
        }

        $packageContent = json_decode(file_get_contents($packageJson), true);
        $dependencies = array_merge(
            $packageContent['dependencies'] ?? [],
            $packageContent['devDependencies'] ?? []
        );

        $requiredPackages = [
            '@inertiajs/vue3',
            'vue',
            'primevue',
            'tailwindcss',
        ];

        foreach ($requiredPackages as $package) {
            if (isset($dependencies[$package])) {
                $this->checkPass($package);
            } else {
                $this->checkFail("{$package} not in package.json");
            }
        }

        // Check if node_modules exists
        if (is_dir(base_path('node_modules'))) {
            $this->checkPass('node_modules installed');
        } else {
            $this->checkWarn('node_modules not found', 'Run: npm install');
        }
    }

    protected function checkTestConfiguration(): void
    {
        $this->components->twoColumnDetail('<fg=blue>Test Configuration</>', '');

        $pestPath = base_path('tests/Pest.php');

        if (! file_exists($pestPath)) {
            $this->checkFail('tests/Pest.php not found');

            return;
        }

        $content = file_get_contents($pestPath);
        $hasMissingConfig = false;

        // Check for RefreshDatabase in Feature tests
        if (str_contains($content, 'RefreshDatabase') && str_contains($content, "in('Feature')")) {
            $this->checkPass('RefreshDatabase configured for Feature tests');
        } else {
            $this->checkFail('RefreshDatabase not configured for Feature tests');
            $hasMissingConfig = true;
        }

        // Check for RefreshDatabase in Browser tests
        if (str_contains($content, 'RefreshDatabase') && str_contains($content, "in('Browser')")) {
            $this->checkPass('RefreshDatabase configured for Browser tests');
        } else {
            $this->checkFail('RefreshDatabase not configured for Browser tests');
            $hasMissingConfig = true;
        }

        // Show code snippet if configuration is missing
        if ($hasMissingConfig) {
            $this->newLine();
            $this->line('  <fg=yellow>Add to tests/Pest.php:</>');
            $this->line('  <fg=gray>pest()->extend(Tests\TestCase::class)</>');
            $this->line('  <fg=gray>    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)</>');
            $this->line('  <fg=gray>    ->in(\'Feature\');</>');
            $this->newLine();
            $this->line('  <fg=gray>pest()->extend(Tests\TestCase::class)</>');
            $this->line('  <fg=gray>    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)</>');
            $this->line('  <fg=gray>    ->group(\'browser\')</>');
            $this->line('  <fg=gray>    ->in(\'Browser\');</>');
            $this->newLine();
        }

        // Check phpunit.xml for Browser testsuite
        $phpunitPath = base_path('phpunit.xml');
        if (file_exists($phpunitPath)) {
            $phpunitContent = file_get_contents($phpunitPath);

            if (str_contains($phpunitContent, 'Browser')) {
                $this->checkPass('phpunit.xml has Browser testsuite');
            } else {
                $this->checkFail('phpunit.xml missing Browser testsuite');
                $this->newLine();
                $this->line('  <fg=yellow>Add to phpunit.xml in <testsuites>:</>');
                $this->line('  <fg=gray><testsuite name="Browser"></>');
                $this->line('  <fg=gray>    <directory>tests/Browser</directory></>');
                $this->line('  <fg=gray></testsuite></>');
                $this->newLine();
            }
        }

        // Check vite.config.js
        $vitePath = base_path('vite.config.js');
        if (file_exists($vitePath)) {
            $viteContent = file_get_contents($vitePath);

            if (str_contains($viteContent, '@vitejs/plugin-vue') || str_contains($viteContent, 'plugin-vue')) {
                $this->checkPass('vite.config.js has Vue plugin');
            } else {
                $this->checkFail('vite.config.js missing Vue plugin');
            }

            if (str_contains($viteContent, 'tailwindcss')) {
                $this->checkPass('vite.config.js has Tailwind plugin');
            } else {
                $this->checkFail('vite.config.js missing Tailwind plugin');
            }
        }
    }

    protected function checkPass(string $message): void
    {
        $this->passCount++;
        $this->components->twoColumnDetail("  {$message}", '<fg=green>PASS</>');
    }

    protected function checkFail(string $message, ?string $hint = null): void
    {
        $this->failCount++;
        $this->components->twoColumnDetail("  {$message}", '<fg=red>FAIL</>');
        if ($hint) {
            $this->line("    <fg=gray>→ {$hint}</>");
        }
    }

    protected function checkWarn(string $message, ?string $hint = null): void
    {
        $this->warnCount++;
        $this->components->twoColumnDetail("  {$message}", '<fg=yellow>WARN</>');
        if ($hint) {
            $this->line("    <fg=gray>→ {$hint}</>");
        }
    }

    protected function showSummary(): void
    {
        $total = $this->passCount + $this->failCount + $this->warnCount;

        $this->components->twoColumnDetail(
            '<fg=white;options=bold>Summary</>',
            sprintf(
                '<fg=green>%d passed</>, <fg=red>%d failed</>, <fg=yellow>%d warnings</>',
                $this->passCount,
                $this->failCount,
                $this->warnCount
            )
        );

        if ($this->failCount === 0) {
            $this->newLine();
            $this->components->info('Your starter kit integration looks healthy!');
        } else {
            $this->newLine();
            $this->components->error('Some checks failed. Please fix the issues above.');
        }
    }
}
