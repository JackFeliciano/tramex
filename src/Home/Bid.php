<?php

declare(strict_types=1);

namespace Tramex\Home;

/**
 * A carrier's offer to move a load. The lowest offer wins, and a bid above the
 * shipper's ceiling never enters the running.
 */
final class Bid
{
    public function __construct(
        public readonly string $loadId,
        public readonly string $carrier,
        public readonly int $amountCents,
        public readonly int $transitDays,
    ) {
    }
}
