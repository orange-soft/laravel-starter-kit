# OrangeSoft Laravel Starter Kit

A Laravel starter kit installer that scaffolds a complete admin application with authentication, user management, and role-based access control.

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Node.js 18+
- PostgreSQL / MySQL / SQLite

## Installation

```bash
# On a fresh Laravel installation
composer require orange-soft/laravel-starter-kit --dev

# Install core features
php artisan os:starter:install

# Install with optional features
php artisan os:starter:install --with=media --with=backup --with=activitylog

# Force overwrite existing files
php artisan os:starter:install --force
```

## Post-Installation

```bash
npm install
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminUserSeeder

# Start development server
composer dev
```

Default admin credentials:
- Email: `admin@example.com`
- Password: `password`

## What's Included

### Stack
- **Inertia.js v2** - Server-side routing with Vue frontend
- **Vue 3** - Composition API with `<script setup>`
- **Tailwind CSS v4** - CSS-first configuration (14px compact admin UI)
- **PrimeVue** - UI component library with Aura preset
- **Laravel Wayfinder** - Type-safe routing
- **Spatie Permission** - Role-based access control

### Features

#### Authentication
- Login with email/password
- "Remember me" functionality
- Forgot password (email link)
- Reset password
- Email verification
- **Temporary password flow**: New users must change password on first login

#### User Management
- List users with search and pagination
- Create user with role assignment
- Edit user with role assignment
- Delete user (with self-delete protection)

#### Navigation Context
- **Automatic pagination preservation**: Users return to the same page after CRUD operations
- Session-based URL storage for index pages
- Works automatically with all resource controllers

#### Admin UI
- **14px base font** for compact, professional appearance
- **Breadcrumbs** with automatic title fallback
- Sidebar navigation
- Topbar with user menu
- Toast notifications
- Confirmation dialogs

### Files Structure

```
app/
├── Enums/
│   └── RoleName.php                    # Role enum (customize per app)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── ChangePasswordController.php
│   │   │   ├── ForgotPasswordController.php
│   │   │   ├── LoginController.php
│   │   │   ├── LogoutController.php
│   │   │   ├── ResetPasswordController.php
│   │   │   └── VerifyEmailController.php
│   │   ├── Traits/
│   │   │   └── RedirectsToStoredIndex.php  # Navigation context trait
│   │   ├── ProfileController.php
│   │   └── UserController.php
│   ├── Middleware/
│   │   ├── EnsurePasswordIsNotTemporary.php
│   │   ├── HandleInertiaRequests.php
│   │   └── HandleNavigationContext.php     # Pagination preservation
│   └── Requests/
│       ├── Auth/
│       │   ├── ChangePasswordRequest.php
│       │   ├── ForgotPasswordRequest.php
│       │   ├── LoginRequest.php
│       │   └── ResetPasswordRequest.php
│       ├── ProfileUpdateRequest.php
│       ├── UserStoreRequest.php
│       └── UserUpdateRequest.php
├── Mail/
│   └── AppMailable.php                 # Base mailable class
├── Models/
│   ├── Traits/
│   │   └── HasUuidRouteKey.php
│   └── User.php                        # With HasRoles, temp password support
└── Notifications/
    ├── Auth/
    │   ├── ResetPasswordNotification.php
    │   └── VerifyEmailNotification.php
    └── AppNotification.php             # Base notification class

bootstrap/
└── app.php                             # Middleware registration

config/
└── os.php                              # Kit configuration

database/
├── migrations/
│   └── 0001_01_01_000010_add_uuid_and_temp_password_to_users_table.php
└── seeders/
    ├── AdminUserSeeder.php
    ├── DatabaseSeeder.php
    └── RoleSeeder.php

resources/
├── css/
│   ├── app.css                         # Tailwind + PrimeUI + Typography
│   └── fonts.css                       # Custom fonts
├── js/
│   ├── layouts/
│   │   ├── AdminLayout.vue             # With breadcrumbs, Head component
│   │   └── AuthLayout.vue
│   ├── components/
│   │   ├── AppShell.vue
│   │   ├── ConfirmDialog.vue
│   │   ├── FormError.vue
│   │   ├── Sidebar.vue
│   │   ├── Toast.vue
│   │   └── Topbar.vue
│   ├── pages/
│   │   ├── auth/
│   │   │   ├── ChangePassword.vue
│   │   │   ├── ForgotPassword.vue
│   │   │   ├── Login.vue
│   │   │   ├── ResetPassword.vue
│   │   │   └── VerifyEmail.vue
│   │   ├── dev/
│   │   │   └── Typography.vue          # Typography preview page
│   │   ├── profile/
│   │   │   └── Edit.vue
│   │   ├── users/
│   │   │   ├── Create.vue
│   │   │   ├── Edit.vue
│   │   │   └── Index.vue
│   │   └── Dashboard.vue
│   ├── app.js
│   └── bootstrap.js
└── views/
    ├── app.blade.php
    └── vendor/
        ├── mail/                       # Custom mail templates
        └── notifications/

routes/
├── admin.php                           # User management routes
├── auth.php                            # Authentication routes
└── web.php                             # Modified to include above

tests/
├── Browser/
│   ├── AuthTest.php
│   ├── ProfileTest.php
│   └── UserManagementTest.php
└── Feature/
    ├── Auth/
    │   ├── ChangePasswordTest.php
    │   ├── EmailVerificationTest.php
    │   ├── ForgotPasswordTest.php
    │   ├── LoginTest.php
    │   ├── LogoutTest.php
    │   └── ResetPasswordTest.php
    ├── Profile/
    │   └── ProfileTest.php
    ├── MiddlewareTest.php
    ├── NotificationsTest.php
    ├── RolesTest.php
    └── UserManagementTest.php
```

## Customization

### Roles

Edit `app/Enums/RoleName.php` to define your application's roles:

```php
enum RoleName: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';
}
```

Then re-run the seeder:

```bash
php artisan db:seed --class=RoleSeeder
```

### Navigation Context

The `HandleNavigationContext` middleware runs globally and preserves pagination/filter state. In controllers, use the `RedirectsToStoredIndex` trait:

```php
use App\Http\Controllers\Traits\RedirectsToStoredIndex;

class ProductController extends Controller
{
    use RedirectsToStoredIndex;

    public function store(ProductStoreRequest $request)
    {
        $request->persist();

        // Redirects to stored URL (e.g., /products?page=3&search=widget)
        return $this->redirectToIndex('products.index', 'Product created.');
    }
}
```

### Breadcrumbs

Pass breadcrumbs to `AdminLayout`:

```vue
<AdminLayout
    title="Edit Product"
    :breadcrumbs="[
        { label: 'Products', href: '/products' },
        { label: product.name }
    ]"
>
```

If no breadcrumbs provided, the title is used as fallback.

## Optional Features

### Media Library (`--with=media`)
- Installs `spatie/laravel-medialibrary`
- Publishes config and migrations

### Backup (`--with=backup`)
- Installs `spatie/laravel-backup`
- Publishes config

### Activity Log (`--with=activitylog`)
- Installs `spatie/laravel-activitylog`
- Publishes config and migrations
- Includes `LogsActivity` trait

## Testing

```bash
# Run all tests
composer test

# Run specific test file
php artisan test tests/Feature/UserManagementTest.php

# Run browser tests (requires Chrome)
php artisan test tests/Browser
```

## Development Scripts

```bash
# Start all services (server, queue, logs, vite)
composer dev

# Initial setup
composer setup
```

## License

Proprietary - Orange Soft
