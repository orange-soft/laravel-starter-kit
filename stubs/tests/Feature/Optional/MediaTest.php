<?php

use Spatie\MediaLibrary\MediaLibraryServiceProvider;

describe('Media Library', function () {
    it('has media library service provider registered', function () {
        expect(app()->getProviders(MediaLibraryServiceProvider::class))
            ->not->toBeEmpty();
    });

    it('has media library config published', function () {
        expect(config('media-library'))->not->toBeNull();
    });

    it('has media library migrations', function () {
        $migrations = glob(database_path('migrations/*_create_media_table.php'));

        expect($migrations)->not->toBeEmpty();
    });
});
