<?php

use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Models\Activity;

describe('Activity Log', function () {
    it('has activity log service provider registered', function () {
        expect(app()->getProviders(ActivitylogServiceProvider::class))
            ->not->toBeEmpty();
    });

    it('has activity log config published', function () {
        expect(config('activitylog'))->not->toBeNull();
        expect(config('activitylog.activity_model'))->toBe(Activity::class);
    });

    it('has activity log table migrated', function () {
        expect(Schema::hasTable('activity_log'))->toBeTrue();
    });

    it('can log activity', function () {
        activity()->log('Test activity');

        expect(Activity::where('description', 'Test activity')->exists())->toBeTrue();
    });
});
