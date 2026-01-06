<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\BackupServiceProvider;

describe('Backup', function () {
    it('has backup service provider registered', function () {
        expect(app()->getProviders(BackupServiceProvider::class))
            ->not->toBeEmpty();
    });

    it('has backup config published', function () {
        expect(config('backup'))->not->toBeNull();
        expect(config('backup.backup.name'))->not->toBeNull();
    });

    it('has backup commands available', function () {
        $commands = Artisan::all();

        expect($commands)->toHaveKey('backup:run');
        expect($commands)->toHaveKey('backup:list');
        expect($commands)->toHaveKey('backup:clean');
        expect($commands)->toHaveKey('backup:monitor');
    });
});
