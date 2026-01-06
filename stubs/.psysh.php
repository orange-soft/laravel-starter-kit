<?php

/**
 * PsySH project config (used by `php artisan tinker`).
 * Auto-disable pcntl when using PostgreSQL to avoid fork-related crashes.
 */

$detectPg = function (): bool {
    // 1) Standard Laravel env
    $db = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? null);
    if (is_string($db) && strtolower($db) === 'pgsql') {
        return true;
    }

    // 2) DATABASE_URL scheme (common in cloud envs)
    $url = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);
    if (is_string($url) && $url !== '') {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (in_array($scheme, ['pgsql', 'postgres', 'postgresql'], true)) {
            return true;
        }
    }

    // 3) Fallback heuristic: if pgsql driver is present AND nothing says otherwise
    // (You can remove this if you also use MySQL locally.)
    // return extension_loaded('pdo_pgsql') || extension_loaded('pgsql');

    return false;
};

$isPg = $detectPg();

return [
    // Only disable for PostgreSQL
    'usePcntl' => $isPg ? false : true,
];
