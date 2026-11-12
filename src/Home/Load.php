<?php

declare(strict_types=1);

namespace Tramex\Home;

/**
 * A load is one shipment a shipper needs moved. It carries an origin and a
 * destination, the weight, the pickup window and a ceiling the shipper will pay.
 * Every amount is held in whole cents.
 */
final class Load
{
    public function __construct(
        public readonly string $id,
        public readonly string $shipper,
        public readonly string $origin,
        public readonly string $destination,
        public readonly int $weightKg,
        public readonly string $pickupDay,
        public readonly int $ceilingCents,
    ) {
    }
}
