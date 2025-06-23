<?php

use Illuminate\Support\Facades\DB;
use Pan\Adapters\Laravel\Repositories\DatabaseAnalyticsRepository;
use Pan\Enums\EventType;
use Pan\PanConfiguration;

beforeEach(function (): void {
    // Drop and recreate the analytics table for testing
    DB::statement('DROP TABLE IF EXISTS pan_analytics');
    DB::statement('CREATE TABLE pan_analytics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        impressions INTEGER DEFAULT 0,
        hovers INTEGER DEFAULT 0,
        clicks INTEGER DEFAULT 0,
        tenant_id VARCHAR(255) NULL,
        organization_id VARCHAR(255) NULL
    )');
});

afterEach(function (): void {
    DB::statement('DROP TABLE IF EXISTS pan_analytics');
});

it('can store analytics without tenant', function (): void {
    $repository = new DatabaseAnalyticsRepository(PanConfiguration::instance());

    $repository->increment('help-modal', EventType::CLICK);

    $analytics = $repository->all();

    expect($analytics)->toHaveCount(1);
    expect($analytics[0]->name)->toBe('help-modal');
    expect($analytics[0]->clicks)->toBe(1);
    expect($analytics[0]->tenantId)->toBeNull();
});

it('can store analytics with tenant', function (): void {
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');

    $repository = new DatabaseAnalyticsRepository(PanConfiguration::instance());

    $repository->increment('help-modal', EventType::CLICK);

    $analytics = $repository->all();

    expect($analytics)->toHaveCount(1);
    expect($analytics[0]->name)->toBe('help-modal');
    expect($analytics[0]->clicks)->toBe(1);
    expect($analytics[0]->tenantId)->toBe('store:1234');
});

it('isolates analytics between tenants', function (): void {
    $repository = new DatabaseAnalyticsRepository(PanConfiguration::instance());

    // Create analytics for tenant 1
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');
    $repository->increment('help-modal', EventType::CLICK);

    // Create analytics for tenant 2
    PanConfiguration::setTenantId('store:5678');
    $repository->increment('help-modal', EventType::CLICK);
    $repository->increment('contact-modal', EventType::IMPRESSION);

    // Check tenant 1 analytics
    PanConfiguration::setTenantId('store:1234');
    $tenant1Analytics = $repository->all();
    expect($tenant1Analytics)->toHaveCount(1);
    expect($tenant1Analytics[0]->name)->toBe('help-modal');
    expect($tenant1Analytics[0]->tenantId)->toBe('store:1234');

    // Check tenant 2 analytics
    PanConfiguration::setTenantId('store:5678');
    $tenant2Analytics = $repository->all();
    expect($tenant2Analytics)->toHaveCount(2);
    expect($tenant2Analytics[0]->tenantId)->toBe('store:5678');
    expect($tenant2Analytics[1]->tenantId)->toBe('store:5678');
});

it('respects max analytics per tenant', function (): void {
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');
    PanConfiguration::maxAnalytics(2);

    $repository = new DatabaseAnalyticsRepository(PanConfiguration::instance());

    // Create 3 different analytics (should only store 2)
    $repository->increment('modal-1', EventType::CLICK);
    $repository->increment('modal-2', EventType::CLICK);
    $repository->increment('modal-3', EventType::CLICK); // This should be ignored

    $analytics = $repository->all();
    expect($analytics)->toHaveCount(2);
});

it('counts analytics correctly per tenant for max limit', function (): void {
    PanConfiguration::maxAnalytics(1);
    $repository = new DatabaseAnalyticsRepository(PanConfiguration::instance());

    // Tenant 1 can create 1 analytic
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');
    $repository->increment('modal-1', EventType::CLICK);

    // Tenant 2 can also create 1 analytic (separate from tenant 1)
    PanConfiguration::setTenantId('store:5678');
    $repository->increment('modal-2', EventType::CLICK);

    // Verify each tenant has their analytic
    PanConfiguration::setTenantId('store:1234');
    expect($repository->all())->toHaveCount(1);

    PanConfiguration::setTenantId('store:5678');
    expect($repository->all())->toHaveCount(1);
});

it('can flush analytics for specific tenant only', function (): void {
    $repository = new DatabaseAnalyticsRepository(PanConfiguration::instance());

    // Create analytics for multiple tenants
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');
    $repository->increment('help-modal', EventType::CLICK);

    PanConfiguration::setTenantId('store:5678');
    $repository->increment('contact-modal', EventType::CLICK);

    // Flush only tenant 1
    PanConfiguration::setTenantId('store:1234');
    $repository->flush();

    // Tenant 1 should have no analytics
    expect($repository->all())->toHaveCount(0);

    // Tenant 2 should still have analytics
    PanConfiguration::setTenantId('store:5678');
    expect($repository->all())->toHaveCount(1);
});

it('can flush all analytics when no tenant is set', function (): void {
    $repository = new DatabaseAnalyticsRepository(PanConfiguration::instance());

    // Create analytics with and without tenants
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');
    $repository->increment('help-modal', EventType::CLICK);

    PanConfiguration::setTenantKey(null);
    PanConfiguration::setTenantId(null);
    $repository->increment('global-modal', EventType::CLICK);

    // Flush all
    $repository->flush();

    // All analytics should be gone
    expect($repository->all())->toHaveCount(0);

    // Even with tenant set, should be empty
    PanConfiguration::setTenantKey('tenant_id');
    PanConfiguration::setTenantId('store:1234');
    expect($repository->all())->toHaveCount(0);
});

it('can use different tenant key column names', function (): void {
    PanConfiguration::setTenantKey('organization_id');
    PanConfiguration::setTenantId('org_456');

    $repository = new DatabaseAnalyticsRepository(PanConfiguration::instance());

    $repository->increment('help-modal', EventType::CLICK);

    // Verify the data was stored with the correct column
    $result = DB::table('pan_analytics')->first();
    expect($result->organization_id)->toBe('org_456');

    $analytics = $repository->all();
    expect($analytics[0]->tenantId)->toBe('org_456');
});
