<?php

declare(strict_types=1);

namespace Pan\Adapters\Laravel\Repositories;

use Illuminate\Support\Facades\DB;
use Pan\Contracts\AnalyticsRepository;
use Pan\Enums\EventType;
use Pan\PanConfiguration;
use Pan\ValueObjects\Analytic;

/**
 * @internal
 */
final readonly class DatabaseAnalyticsRepository implements AnalyticsRepository
{
    /**
     * Creates a new analytics repository instance.
     */
    public function __construct(private PanConfiguration $config)
    {
        //
    }

    /**
     * Returns all analytics.
     *
     * @return array<int, Analytic>
     */
    public function all(): array
    {
        $config = $this->config->toArray();
        $query = DB::table('pan_analytics');

        if ($config['tenant_key'] !== null && $config['tenant_id'] !== null) {
            $query->where($config['tenant_key'], $config['tenant_id']);
        }

        /** @var array<int, Analytic> $all */
        $all = $query->get()->map(fn (mixed $analytic): Analytic => new Analytic(
            id: (int) $analytic->id,
            name: $analytic->name,
            impressions: (int) $analytic->impressions,
            hovers: (int) $analytic->hovers,
            clicks: (int) $analytic->clicks,
            tenantId: $config['tenant_key'] !== null ? $analytic->{$config['tenant_key']} ?? null : null,
        ))->toArray();

        return $all;
    }

    /**
     * Increments the given event for the given analytic.
     */
    public function increment(string $name, EventType $event): void
    {
        [
            'allowed_analytics' => $allowedAnalytics,
            'max_analytics' => $maxAnalytics,
            'tenant_key' => $tenantKey,
            'tenant_id' => $tenantId,
        ] = $this->config->toArray();

        if (count($allowedAnalytics) > 0 && ! in_array($name, $allowedAnalytics, true)) {
            return;
        }

        $query = DB::table('pan_analytics')->where('name', $name);

        if ($tenantKey !== null && $tenantId !== null) {
            $query->where($tenantKey, $tenantId);
        }

        if ($query->count() === 0) {
            $countQuery = DB::table('pan_analytics');

            if ($tenantKey !== null && $tenantId !== null) {
                $countQuery->where($tenantKey, $tenantId);
            }

            if ($countQuery->count() < $maxAnalytics) {
                $insertData = ['name' => $name, $event->column() => 1];

                if ($tenantKey !== null && $tenantId !== null) {
                    $insertData[$tenantKey] = $tenantId;
                }

                DB::table('pan_analytics')->insert($insertData);
            }

            return;
        }

        $query->increment($event->column());
    }

    /**
     * Flush all analytics.
     */
    public function flush(): void
    {
        $config = $this->config->toArray();

        if ($config['tenant_key'] !== null && $config['tenant_id'] !== null) {
            DB::table('pan_analytics')
                ->where($config['tenant_key'], $config['tenant_id'])
                ->delete();
        } else {
            DB::table('pan_analytics')->truncate();
        }
    }
}
