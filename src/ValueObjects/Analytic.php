<?php

declare(strict_types=1);

namespace Pan\ValueObjects;

/**
 * @internal
 */
final readonly class Analytic
{
    /**
     * Returns all analytics.
     *
     * @return array<int, Analytic>
     */
    public function __construct(
        public int $id,
        public string $name,
        public int $impressions,
        public int $hovers,
        public int $clicks,
        public ?string $tenantId = null,
    ) {
        //
    }

    /**
     * Returns the analytic as an array.
     *
     * @return array{id: int, name: string, impressions: int, hovers: int, clicks: int, tenant_id: ?string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'impressions' => $this->impressions,
            'hovers' => $this->hovers,
            'clicks' => $this->clicks,
            'tenant_id' => $this->tenantId,
        ];
    }
}
