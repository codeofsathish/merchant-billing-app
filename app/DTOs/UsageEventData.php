<?php
namespace App\DTOs;

use Carbon\CarbonImmutable;

final readonly class UsageEventData
{
    public function __construct(
        public int $merchantId,
        public int $customerId,
        public string $eventKey,
        public CarbonImmutable $occurredAt,
        public int $units,
        public string $type = 'api',
        public ?array $metadata = null,
    ) {}
}
