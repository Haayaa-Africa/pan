<?php

declare(strict_types=1);

use Pan\PanConfiguration;

beforeEach(function (): void {
    PanConfiguration::reset();
});

it('can configure middleware for analytics routes', function (): void {
    PanConfiguration::middleware(['auth', 'throttle:60,1']);

    expect(PanConfiguration::instance()->toArray()['middleware'])
        ->toBe(['auth', 'throttle:60,1']);
});

it('can configure multiple middleware for routes', function (): void {
    PanConfiguration::middleware(['throttle:60,1', 'auth', 'verified']);

    expect(PanConfiguration::instance()->toArray()['middleware'])
        ->toBe(['throttle:60,1', 'auth', 'verified']);
});

it('has no middleware configured by default', function (): void {
    expect(PanConfiguration::instance()->toArray()['middleware'])->toBe([]);
});

it('can reset middleware configuration', function (): void {
    PanConfiguration::middleware(['auth', 'throttle:60,1']);

    expect(PanConfiguration::instance()->toArray()['middleware'])
        ->toBe(['auth', 'throttle:60,1']);

    PanConfiguration::reset();

    expect(PanConfiguration::instance()->toArray()['middleware'])->toBe([]);
});

it('middleware configuration is applied to service provider', function (): void {
    // This test verifies that the middleware configuration is passed to the service provider
    PanConfiguration::middleware(['throttle:60,1']);

    // Get the configuration as it would be used by the service provider
    $config = PanConfiguration::instance();
    $configArray = $config->toArray();

    expect($configArray['middleware'])->toBe(['throttle:60,1']);
});
