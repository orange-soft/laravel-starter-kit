<?php

namespace OrangeSoft\LaravelStarterKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;

class InstallCommand extends Command
{
    protected $signature = 'os:starter:install
                            {--with=* : Optional features to install (media, backup, activitylog)}
                            {--force : Overwrite existing files}';

    protected $description = 'Install the OrangeSoft Laravel Starter Kit';

    protected Filesystem $files;

    protected array $optionalFeatures = [
        'media' => 'Media library (spatie/laravel-medialibrary)',
        'backup' => 'Database & file backup (spatie/laravel-backup)',
        'activitylog' => 'Activity logging (spatie/laravel-activitylog)',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->files = new Filesystem;
    }

    public function handle(): int
    {
        $this->components->info('Installing OrangeSoft Laravel Starter Kit...');

        // Determine optional features
        $features = $this->option('with') ?: [];
        if (empty($features) && $this->input->isInteractive()) {
            $features = $this->promptForFeatures();
        }

        // Core installation (always runs in this order)
        $this->installStack();
        $this->installLayouts();
        $this->installAuth();
        $this->installUserManagement();
        $this->installRoles();
        $this->installMail();
        $this->installTests();

        // Optional features
        if (in_array('media', $features)) {
            $this->installMedia();
        }

        if (in_array('backup', $features)) {
            $this->installBackup();
        }

        if (in_array('activitylog', $features)) {
            $this->installActivityLog();
        }

        // Post-install tasks
        $this->runPostInstall();

        $this->components->info('OrangeSoft Laravel Starter Kit installed successfully!');
        $this->newLine();
        $this->components->bulletList([
            'Run <comment>npm install</comment> to install dependencies',
            'Run <comment>php artisan migrate</comment> to run migrations',
            'Run <comment>php artisan db:seed --class=RoleSeeder</comment> to seed roles',
            'Run <comment>php artisan db:seed --class=AdminUserSeeder</comment> to create admin user',
            'Run <comment>composer dev</comment> to start development server',
        ]);

        return self::SUCCESS;
    }

    protected function promptForFeatures(): array
    {
        return multiselect(
            label: 'Which optional features would you like to install?',
            options: $this->optionalFeatures,
            default: [],
            hint: 'Core features (auth, users, roles) are always installed. Use space to select, enter to confirm.'
        );
    }

    // =========================================================================
    // CORE INSTALLATION METHODS
    // =========================================================================

    protected function installStack(): void
    {
        $this->components->task('Installing stack (Inertia, Vue, Tailwind, PrimeVue)', function () {
            // Install composer packages
            $this->requireComposerPackages([
                'inertiajs/inertia-laravel:^2.0',
                'laravel/wayfinder:^0.1',
                'spatie/laravel-permission:^6.0',
            ]);

            // Update package.json with npm dependencies
            $this->updateNodePackages(function ($packages) {
                return [
                    '@inertiajs/vue3' => '^2.0',
                    '@primeuix/themes' => '^2.0',
                    '@vitejs/plugin-vue' => '^6.0',
                    '@vueuse/core' => '^14.0',
                    'primeicons' => '^7.0',
                    'primevue' => '^4.0',
                    'tailwindcss-primeui' => '^0.6',
                    'vue' => '^3.5',
                ] + $packages;
            });

            $this->updateNodePackages(function ($packages) {
                return [
                    '@laravel/vite-plugin-wayfinder' => '^0.1',
                    '@tailwindcss/vite' => '^4.0',
                    '@types/node' => '^22.0',
                    'tailwindcss' => '^4.0',
                ] + $packages;
            }, dev: true);

            // Copy stack files
            $this->copyFile('vite.config.js', base_path('vite.config.js'));
            $this->copyFile('resources/js/app.js', resource_path('js/app.js'));
            $this->copyFile('resources/js/bootstrap.js', resource_path('js/bootstrap.js'));
            $this->copyFile('resources/css/app.css', resource_path('css/app.css'));
            $this->copyFile('resources/views/app.blade.php', resource_path('views/app.blade.php'));

            // Copy middleware
            $this->ensureDirectoryExists(app_path('Http/Middleware'));
            $this->copyFile('app/Http/Middleware/HandleInertiaRequests.php', app_path('Http/Middleware/HandleInertiaRequests.php'));
            $this->copyFile('app/Http/Middleware/HandleNavigationContext.php', app_path('Http/Middleware/HandleNavigationContext.php'));

            // Copy public assets (favicons)
            $this->copyFile('public/favicon.ico', public_path('favicon.ico'));
            $this->copyFile('public/favicon.svg', public_path('favicon.svg'));
            $this->copyFile('public/favicon-96x96.png', public_path('favicon-96x96.png'));
            $this->copyFile('public/apple-touch-icon.png', public_path('apple-touch-icon.png'));
            $this->copyFile('public/site.webmanifest', public_path('site.webmanifest'));
            $this->copyFile('public/web-app-manifest-192x192.png', public_path('web-app-manifest-192x192.png'));
            $this->copyFile('public/web-app-manifest-512x512.png', public_path('web-app-manifest-512x512.png'));

            // Copy fonts
            $this->copyFile('resources/css/fonts.css', resource_path('css/fonts.css'));
            $this->copyDirectory('public/fonts', public_path('fonts'));

            // Copy config
            $this->copyFile('config/os.php', config_path('os.php'));
            $this->copyFile('.psysh.php', base_path('.psysh.php'));

            // Update bootstrap/app.php for middleware
            $this->installMiddleware();

            // Update composer.json scripts
            $this->updateComposerScripts();

            return true;
        });
    }

    protected function installLayouts(): void
    {
        $this->components->task('Installing layouts and components', function () {
            // Layouts
            $this->ensureDirectoryExists(resource_path('js/layouts'));
            $this->copyFile('resources/js/layouts/AuthLayout.vue', resource_path('js/layouts/AuthLayout.vue'));
            $this->copyFile('resources/js/layouts/AdminLayout.vue', resource_path('js/layouts/AdminLayout.vue'));

            // Components
            $this->ensureDirectoryExists(resource_path('js/components'));
            $this->copyFile('resources/js/components/AppShell.vue', resource_path('js/components/AppShell.vue'));
            $this->copyFile('resources/js/components/Sidebar.vue', resource_path('js/components/Sidebar.vue'));
            $this->copyFile('resources/js/components/Topbar.vue', resource_path('js/components/Topbar.vue'));
            $this->copyFile('resources/js/components/Toast.vue', resource_path('js/components/Toast.vue'));
            $this->copyFile('resources/js/components/ConfirmDialog.vue', resource_path('js/components/ConfirmDialog.vue'));
            $this->copyFile('resources/js/components/FormError.vue', resource_path('js/components/FormError.vue'));

            // Dev pages
            $this->ensureDirectoryExists(resource_path('js/pages/dev'));
            $this->copyFile('resources/js/pages/dev/Typography.vue', resource_path('js/pages/dev/Typography.vue'));

            // Dashboard
            $this->copyFile('resources/js/pages/Dashboard.vue', resource_path('js/pages/Dashboard.vue'));

            return true;
        });
    }

    protected function installAuth(): void
    {
        $this->components->task('Installing authentication', function () {
            // Controllers
            $this->ensureDirectoryExists(app_path('Http/Controllers/Auth'));
            $this->copyFile('app/Http/Controllers/Auth/LoginController.php', app_path('Http/Controllers/Auth/LoginController.php'));
            $this->copyFile('app/Http/Controllers/Auth/LogoutController.php', app_path('Http/Controllers/Auth/LogoutController.php'));
            $this->copyFile('app/Http/Controllers/Auth/ForgotPasswordController.php', app_path('Http/Controllers/Auth/ForgotPasswordController.php'));
            $this->copyFile('app/Http/Controllers/Auth/ResetPasswordController.php', app_path('Http/Controllers/Auth/ResetPasswordController.php'));
            $this->copyFile('app/Http/Controllers/Auth/VerifyEmailController.php', app_path('Http/Controllers/Auth/VerifyEmailController.php'));
            $this->copyFile('app/Http/Controllers/Auth/ChangePasswordController.php', app_path('Http/Controllers/Auth/ChangePasswordController.php'));

            // Form Requests
            $this->ensureDirectoryExists(app_path('Http/Requests/Auth'));
            $this->copyFile('app/Http/Requests/Auth/LoginRequest.php', app_path('Http/Requests/Auth/LoginRequest.php'));
            $this->copyFile('app/Http/Requests/Auth/ForgotPasswordRequest.php', app_path('Http/Requests/Auth/ForgotPasswordRequest.php'));
            $this->copyFile('app/Http/Requests/Auth/ResetPasswordRequest.php', app_path('Http/Requests/Auth/ResetPasswordRequest.php'));
            $this->copyFile('app/Http/Requests/Auth/ChangePasswordRequest.php', app_path('Http/Requests/Auth/ChangePasswordRequest.php'));

            // Vue Pages
            $this->ensureDirectoryExists(resource_path('js/pages/auth'));
            $this->copyFile('resources/js/pages/auth/Login.vue', resource_path('js/pages/auth/Login.vue'));
            $this->copyFile('resources/js/pages/auth/ForgotPassword.vue', resource_path('js/pages/auth/ForgotPassword.vue'));
            $this->copyFile('resources/js/pages/auth/ResetPassword.vue', resource_path('js/pages/auth/ResetPassword.vue'));
            $this->copyFile('resources/js/pages/auth/VerifyEmail.vue', resource_path('js/pages/auth/VerifyEmail.vue'));
            $this->copyFile('resources/js/pages/auth/ChangePassword.vue', resource_path('js/pages/auth/ChangePassword.vue'));

            // Routes
            $this->copyFile('routes/auth.php', base_path('routes/auth.php'));

            // Include auth routes in web.php
            $this->installRoutes('auth');

            return true;
        });
    }

    protected function installUserManagement(): void
    {
        $this->components->task('Installing user management', function () {
            // Controller traits
            $this->ensureDirectoryExists(app_path('Http/Controllers/Traits'));
            $this->copyFile('app/Http/Controllers/Traits/RedirectsToStoredIndex.php', app_path('Http/Controllers/Traits/RedirectsToStoredIndex.php'));

            // Controllers
            $this->copyFile('app/Http/Controllers/UserController.php', app_path('Http/Controllers/UserController.php'));
            $this->copyFile('app/Http/Controllers/ProfileController.php', app_path('Http/Controllers/ProfileController.php'));

            // Form Requests
            $this->copyFile('app/Http/Requests/UserStoreRequest.php', app_path('Http/Requests/UserStoreRequest.php'));
            $this->copyFile('app/Http/Requests/UserUpdateRequest.php', app_path('Http/Requests/UserUpdateRequest.php'));
            $this->copyFile('app/Http/Requests/ProfileUpdateRequest.php', app_path('Http/Requests/ProfileUpdateRequest.php'));

            // Vue Pages
            $this->ensureDirectoryExists(resource_path('js/pages/users'));
            $this->copyFile('resources/js/pages/users/Index.vue', resource_path('js/pages/users/Index.vue'));
            $this->copyFile('resources/js/pages/users/Create.vue', resource_path('js/pages/users/Create.vue'));
            $this->copyFile('resources/js/pages/users/Edit.vue', resource_path('js/pages/users/Edit.vue'));

            $this->ensureDirectoryExists(resource_path('js/pages/profile'));
            $this->copyFile('resources/js/pages/profile/Edit.vue', resource_path('js/pages/profile/Edit.vue'));

            // Routes
            $this->copyFile('routes/admin.php', base_path('routes/admin.php'));

            // Include admin routes in web.php
            $this->installRoutes('admin');

            return true;
        });
    }

    protected function installRoles(): void
    {
        $this->components->task('Installing roles and permissions', function () {
            // Enum
            $this->ensureDirectoryExists(app_path('Enums'));
            $this->copyFile('app/Enums/RoleName.php', app_path('Enums/RoleName.php'));

            // User Model with HasRoles
            $this->ensureDirectoryExists(app_path('Models/Traits'));
            $this->copyFile('app/Models/User.php', app_path('Models/User.php'));
            $this->copyFile('app/Models/Traits/HasUuidRouteKey.php', app_path('Models/Traits/HasUuidRouteKey.php'));

            // Middleware for temp password check
            $this->copyFile('app/Http/Middleware/EnsurePasswordIsNotTemporary.php', app_path('Http/Middleware/EnsurePasswordIsNotTemporary.php'));

            // Seeders
            $this->copyFile('database/seeders/DatabaseSeeder.php', database_path('seeders/DatabaseSeeder.php'));
            $this->copyFile('database/seeders/RoleSeeder.php', database_path('seeders/RoleSeeder.php'));
            $this->copyFile('database/seeders/AdminUserSeeder.php', database_path('seeders/AdminUserSeeder.php'));

            // Migrations
            $this->copyFile(
                'database/migrations/0001_01_01_000010_add_uuid_and_temp_password_to_users_table.php',
                database_path('migrations/0001_01_01_000010_add_uuid_and_temp_password_to_users_table.php')
            );

            // Publish permission config and migrations
            $this->runProcess(['php', 'artisan', 'vendor:publish', '--provider=Spatie\Permission\PermissionServiceProvider', '--force']);

            return true;
        });
    }

    protected function installMail(): void
    {
        $this->components->task('Installing mail templates and notifications', function () {
            // Base Mailable class
            $this->ensureDirectoryExists(app_path('Mail'));
            $this->copyFile('app/Mail/AppMailable.php', app_path('Mail/AppMailable.php'));

            // Base Notification class
            $this->ensureDirectoryExists(app_path('Notifications'));
            $this->copyFile('app/Notifications/AppNotification.php', app_path('Notifications/AppNotification.php'));

            // Auth Notifications
            $this->ensureDirectoryExists(app_path('Notifications/Auth'));
            $this->copyFile('app/Notifications/Auth/ResetPasswordNotification.php', app_path('Notifications/Auth/ResetPasswordNotification.php'));
            $this->copyFile('app/Notifications/Auth/VerifyEmailNotification.php', app_path('Notifications/Auth/VerifyEmailNotification.php'));
            $this->copyFile('app/Notifications/Auth/WelcomeNotification.php', app_path('Notifications/Auth/WelcomeNotification.php'));

            // Mail views
            $this->ensureDirectoryExists(resource_path('views/vendor/mail/html/themes'));
            $this->ensureDirectoryExists(resource_path('views/vendor/mail/text'));
            $this->copyDirectory('resources/views/vendor/mail/html', resource_path('views/vendor/mail/html'));
            $this->copyDirectory('resources/views/vendor/mail/text', resource_path('views/vendor/mail/text'));

            // Notification views
            $this->ensureDirectoryExists(resource_path('views/vendor/notifications'));
            $this->copyFile('resources/views/vendor/notifications/email.blade.php', resource_path('views/vendor/notifications/email.blade.php'));

            // Custom stubs for make:mail and make:notification
            $this->ensureDirectoryExists(base_path('stubs'));
            $this->copyFile('stubs/mail.stub', base_path('stubs/mail.stub'));
            $this->copyFile('stubs/markdown-mail.stub', base_path('stubs/markdown-mail.stub'));
            $this->copyFile('stubs/notification.stub', base_path('stubs/notification.stub'));
            $this->copyFile('stubs/markdown-notification.stub', base_path('stubs/markdown-notification.stub'));

            return true;
        });
    }

    protected function installTests(): void
    {
        $this->components->task('Installing tests', function () {
            // Feature tests - Auth
            $this->ensureDirectoryExists(base_path('tests/Feature/Auth'));
            $this->copyFile('tests/Feature/Auth/LoginTest.php', base_path('tests/Feature/Auth/LoginTest.php'));
            $this->copyFile('tests/Feature/Auth/LogoutTest.php', base_path('tests/Feature/Auth/LogoutTest.php'));
            $this->copyFile('tests/Feature/Auth/ForgotPasswordTest.php', base_path('tests/Feature/Auth/ForgotPasswordTest.php'));
            $this->copyFile('tests/Feature/Auth/ResetPasswordTest.php', base_path('tests/Feature/Auth/ResetPasswordTest.php'));
            $this->copyFile('tests/Feature/Auth/EmailVerificationTest.php', base_path('tests/Feature/Auth/EmailVerificationTest.php'));
            $this->copyFile('tests/Feature/Auth/ChangePasswordTest.php', base_path('tests/Feature/Auth/ChangePasswordTest.php'));

            // Feature tests - Profile
            $this->ensureDirectoryExists(base_path('tests/Feature/Profile'));
            $this->copyFile('tests/Feature/Profile/ProfileTest.php', base_path('tests/Feature/Profile/ProfileTest.php'));

            // Feature tests - Roles
            $this->copyFile('tests/Feature/RolesTest.php', base_path('tests/Feature/RolesTest.php'));

            // Feature tests - User Management
            $this->copyFile('tests/Feature/UserManagementTest.php', base_path('tests/Feature/UserManagementTest.php'));

            // Feature tests - Middleware
            $this->copyFile('tests/Feature/MiddlewareTest.php', base_path('tests/Feature/MiddlewareTest.php'));

            // Feature tests - Notifications
            $this->copyFile('tests/Feature/NotificationsTest.php', base_path('tests/Feature/NotificationsTest.php'));

            // Browser tests
            $this->ensureDirectoryExists(base_path('tests/Browser'));
            $this->copyFile('tests/Browser/AuthTest.php', base_path('tests/Browser/AuthTest.php'));
            $this->copyFile('tests/Browser/ProfileTest.php', base_path('tests/Browser/ProfileTest.php'));
            $this->copyFile('tests/Browser/UserManagementTest.php', base_path('tests/Browser/UserManagementTest.php'));

            return true;
        });
    }

    // =========================================================================
    // OPTIONAL FEATURES
    // =========================================================================

    protected function installMedia(): void
    {
        $this->components->task('Installing media library', function () {
            $this->requireComposerPackages(['spatie/laravel-medialibrary:^11.0']);

            // Publish config and migrations
            $this->runProcess(['php', 'artisan', 'vendor:publish', '--provider=Spatie\MediaLibrary\MediaLibraryServiceProvider', '--tag=medialibrary-migrations', '--force']);
            $this->runProcess(['php', 'artisan', 'vendor:publish', '--provider=Spatie\MediaLibrary\MediaLibraryServiceProvider', '--tag=medialibrary-config', '--force']);

            // Copy smoke test
            $this->ensureDirectoryExists(base_path('tests/Feature/Optional'));
            $this->copyFile('tests/Feature/Optional/MediaTest.php', base_path('tests/Feature/Optional/MediaTest.php'));

            return true;
        });
    }

    protected function installBackup(): void
    {
        $this->components->task('Installing backup', function () {
            $this->requireComposerPackages(['spatie/laravel-backup:^9.0']);

            // Publish config
            $this->runProcess(['php', 'artisan', 'vendor:publish', '--provider=Spatie\Backup\BackupServiceProvider', '--force']);

            // Copy smoke test
            $this->ensureDirectoryExists(base_path('tests/Feature/Optional'));
            $this->copyFile('tests/Feature/Optional/BackupTest.php', base_path('tests/Feature/Optional/BackupTest.php'));

            return true;
        });
    }

    protected function installActivityLog(): void
    {
        $this->components->task('Installing activity log', function () {
            $this->requireComposerPackages(['spatie/laravel-activitylog:^4.0']);

            // Publish config and migrations
            $this->runProcess(['php', 'artisan', 'vendor:publish', '--provider=Spatie\Activitylog\ActivitylogServiceProvider', '--tag=activitylog-migrations', '--force']);
            $this->runProcess(['php', 'artisan', 'vendor:publish', '--provider=Spatie\Activitylog\ActivitylogServiceProvider', '--tag=activitylog-config', '--force']);

            // Copy LogsActivity trait
            $this->copyFile('app/Models/Traits/LogsActivity.php', app_path('Models/Traits/LogsActivity.php'));

            // Copy smoke test
            $this->ensureDirectoryExists(base_path('tests/Feature/Optional'));
            $this->copyFile('tests/Feature/Optional/ActivityLogTest.php', base_path('tests/Feature/Optional/ActivityLogTest.php'));

            return true;
        });
    }

    // =========================================================================
    // POST-INSTALL
    // =========================================================================

    protected function runPostInstall(): void
    {
        $this->components->task('Running post-install tasks', function () {
            // Clear config cache
            $this->runProcess(['php', 'artisan', 'config:clear']);

            return true;
        });
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    protected function installMiddleware(): void
    {
        // Copy the full bootstrap/app.php file from stubs
        // The stub includes all middleware configuration:
        // - HandleInertiaRequests (web middleware)
        // - HandleNavigationContext (web middleware)
        // - password.not_temporary alias
        // - Spatie permission middleware aliases
        $this->copyFile('bootstrap/app.php', base_path('bootstrap/app.php'));
    }

    protected function installRoutes(string $name): void
    {
        $webRoutes = file_get_contents(base_path('routes/web.php'));

        $requireStatement = "require __DIR__.'/{$name}.php';";

        if (! str_contains($webRoutes, $requireStatement)) {
            file_put_contents(
                base_path('routes/web.php'),
                PHP_EOL . $requireStatement . PHP_EOL,
                FILE_APPEND
            );
        }
    }

    protected function updateComposerScripts(): void
    {
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);

        $composerJson['scripts']['setup'] = [
            'composer install',
            '@php -r "file_exists(\'.env\') || copy(\'.env.example\', \'.env\');"',
            '@php artisan key:generate',
            '@php artisan migrate --force',
            'npm install',
            'npm run build',
        ];

        $composerJson['scripts']['dev'] = [
            'Composer\\Config::disableProcessTimeout',
            'npx concurrently -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" "php artisan serve" "php artisan queue:listen --tries=1" "php artisan pail --timeout=0" "npm run dev" --names=server,queue,logs,vite --kill-others',
        ];

        file_put_contents(
            base_path('composer.json'),
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
    }

    protected function copyFile(string $stub, string $destination): void
    {
        if (! $this->option('force') && $this->files->exists($destination)) {
            return;
        }

        $this->ensureDirectoryExists(dirname($destination));

        $this->files->copy($this->stubPath($stub), $destination);
    }

    protected function copyDirectory(string $stub, string $destination): void
    {
        if (! $this->option('force') && $this->files->isDirectory($destination)) {
            return;
        }

        $this->ensureDirectoryExists($destination);

        $this->files->copyDirectory($this->stubPath($stub), $destination);
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    protected function stubPath(string $path = ''): string
    {
        return __DIR__ . '/../../../stubs/' . $path;
    }

    protected function requireComposerPackages(array $packages): bool
    {
        $command = array_merge(['composer', 'require'], $packages);

        return $this->runProcess($command);
    }

    protected function runProcess(array $command): bool
    {
        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run();

        return $process->isSuccessful();
    }

    protected function updateNodePackages(callable $callback, bool $dev = false): void
    {
        $packageJsonPath = base_path('package.json');

        if (! file_exists($packageJsonPath)) {
            return;
        }

        $packages = json_decode(file_get_contents($packageJsonPath), true);
        $key = $dev ? 'devDependencies' : 'dependencies';

        $packages[$key] = $callback(
            array_key_exists($key, $packages) ? $packages[$key] : []
        );

        ksort($packages[$key]);

        file_put_contents(
            $packageJsonPath,
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }
}
