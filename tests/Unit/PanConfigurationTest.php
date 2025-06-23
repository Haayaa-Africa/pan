<?php

use Pan\PanConfiguration;

it('have a max of 50 analytics by default', function (): void {
    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});

it('can set the max number of analytics to store', function (): void {
    PanConfiguration::maxAnalytics(100);

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 100,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});

it('can set the max number of analytics to unlimited', function (): void {
    PanConfiguration::unlimitedAnalytics();

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => PHP_INT_MAX,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});

it('can set the allowed analytics names to store', function (): void {
    PanConfiguration::allowedAnalytics(['help-modal', 'contact-modal']);

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => ['help-modal', 'contact-modal'],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});

it('sets an empty array of allowed analytics names by default', function (): void {
    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});

it('can set the prefix url', function (): void {
    PanConfiguration::routePrefix('new-pan');

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'new-pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});

it('may reset the configuration to its default values', function (): void {
    PanConfiguration::maxAnalytics(99);
    PanConfiguration::allowedAnalytics(['help-modal', 'contact-modal']);
    PanConfiguration::routePrefix('new-pan');

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 99,
        'allowed_analytics' => ['help-modal', 'contact-modal'],
        'route_prefix' => 'new-pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);

    PanConfiguration::reset();

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});

it('can set the tenant key', function (): void {
    PanConfiguration::setTenantKey('tenant_id');

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => 'tenant_id',
        'tenant_id' => null,
    ]);
});

it('can set the tenant id', function (): void {
    PanConfiguration::setTenantId('store:1234');

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => 'store:1234',
    ]);
});

it('can set both tenant key and id', function (): void {
    PanConfiguration::setTenantKey('organization_id');
    PanConfiguration::setTenantId('org_456');

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => 'organization_id',
        'tenant_id' => 'org_456',
    ]);
});

it('can disable tenant functionality by setting values to null', function (): void {
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');

    // Disable by setting to null
    PanConfiguration::setTenantKey(null);
    PanConfiguration::setTenantId(null);

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});

it('resets tenant configuration when reset is called', function (): void {
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');
    PanConfiguration::maxAnalytics(100);

    PanConfiguration::reset();

    expect(PanConfiguration::instance()->toArray())->toBe([
        'max_analytics' => 50,
        'allowed_analytics' => [],
        'route_prefix' => 'pan',
        'tenant_key' => null,
        'tenant_id' => null,
    ]);
});
