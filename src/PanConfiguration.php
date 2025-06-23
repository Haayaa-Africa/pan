<?php

declare(strict_types=1);

namespace Pan;

final class PanConfiguration
{
    /**
     * The Pan configuration's instance.
     */
    private static ?self $instance = null;

    /**
     * Creates a new Pan configuration instance.
     *
     * @param  array<int, string>  $allowedAnalytics
     */
    private function __construct(
        private int $maxAnalytics = 50,
        private array $allowedAnalytics = [],
        private string $routePrefix = 'pan',
        private ?string $tenantKey = null,
        private ?string $tenantId = null,
    ) {
        //
    }

    /**
     * Returns the Pan configuration's instance.
     *
     * @internal
     */
    public static function instance(): self
    {
        return self::$instance ??= new self;
    }

    /**
     * Sets the maximum number of analytics to store.
     *
     * @internal
     */
    public function setMaxAnalytics(int $number): void
    {
        $this->maxAnalytics = $number;
    }

    /**
     * Sets the allowed analytics names to store.
     *
     * @param  array<int, string>  $names
     *
     * @internal
     */
    public function setAllowedAnalytics(array $names): void
    {
        $this->allowedAnalytics = $names;
    }

    /**
     * Sets the route prefix to be used.
     *
     * @internal
     */
    public function setRoutePrefix(string $prefix): void
    {
        $this->routePrefix = $prefix;
    }

    /**
     * Sets the tenant key (column name).
     *
     * @internal
     */
    private function setInternalTenantKey(?string $key): void
    {
        $this->tenantKey = $key;
    }

    /**
     * Sets the tenant ID.
     *
     * @internal
     */
    private function setInternalTenantId(?string $id): void
    {
        $this->tenantId = $id;
    }

    /**
     * Sets the maximum number of analytics to store.
     */
    public static function maxAnalytics(int $number): void
    {
        self::instance()->setMaxAnalytics($number);
    }

    /**
     * Sets the maximum number of analytics to store to unlimited.
     */
    public static function unlimitedAnalytics(): void
    {
        self::instance()->setMaxAnalytics(PHP_INT_MAX);
    }

    /**
     * Sets the allowed analytics names to store.
     *
     * @param  array<int, string>  $names
     */
    public static function allowedAnalytics(array $names): void
    {
        self::instance()->setAllowedAnalytics($names);
    }

    /**
     * Sets the route prefix to be used.
     *
     * @internal
     */
    public static function routePrefix(string $prefix): void
    {
        self::instance()->setRoutePrefix($prefix);
    }

    /**
     * Sets the tenant key (column name).
     */
    public static function setTenantKey(?string $key): void
    {
        self::instance()->setInternalTenantKey($key);
    }

    /**
     * Sets the tenant ID.
     */
    public static function setTenantId(?string $id): void
    {
        self::instance()->setInternalTenantId($id);
    }

    /**
     * Resets the configuration to its default values.
     *
     * @internal
     */
    public static function reset(): void
    {
        self::maxAnalytics(50);
        self::allowedAnalytics([]);
        self::routePrefix('pan');
        self::setTenantKey(null);
        self::setTenantId(null);
    }

    /**
     * Converts the Pan configuration to an array.
     *
     * @return array{max_analytics: int, allowed_analytics: array<int, string>, route_prefix: string, tenant_key: ?string, tenant_id: ?string}
     *
     * @internal
     */
    public function toArray(): array
    {
        return [
            'max_analytics' => $this->maxAnalytics,
            'allowed_analytics' => $this->allowedAnalytics,
            'route_prefix' => $this->routePrefix,
            'tenant_key' => $this->tenantKey,
            'tenant_id' => $this->tenantId,
        ];
    }
}
