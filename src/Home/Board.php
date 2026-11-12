<?php

declare(strict_types=1);

namespace Tramex\Home;

use InvalidArgumentException;

/**
 * The exchange itself. It holds the loads, the bids and the awards, and it
 * answers the questions the page asks: what is open, what leads a load, what a
 * carrier has won.
 */
final class Board
{
    /** @var array<string, Load> */
    private array $loads = [];

    /** @var array<string, list<Bid>> */
    private array $bids = [];

    /** @var array<string, string> load id to carrier */
    private array $awards = [];

    public function post(Load $load): void
    {
        if ($load->weightKg <= 0) {
            throw new InvalidArgumentException('A load needs a weight above zero.');
        }
        if ($load->ceilingCents <= 0) {
            throw new InvalidArgumentException('A load needs a ceiling above zero.');
        }
        $this->loads[$load->id] = $load;
        $this->bids[$load->id] = [];
    }

    public function load(string $id): Load
    {
        if (!isset($this->loads[$id])) {
            throw new InvalidArgumentException('No load with id ' . $id . '.');
        }

        return $this->loads[$id];
    }

    /**
     * A bid enters only while the load runs and only at or under its ceiling.
     * The carrier's own last bid is replaced, so nobody competes with itself.
     */
    public function bid(Bid $bid): bool
    {
        $load = $this->load($bid->loadId);
        if (isset($this->awards[$bid->loadId])) {
            return false;
        }
        if ($bid->amountCents <= 0 || $bid->amountCents > $load->ceilingCents) {
            return false;
        }
        $kept = [];
        foreach ($this->bids[$bid->loadId] as $existing) {
            if ($existing->carrier !== $bid->carrier) {
                $kept[] = $existing;
            }
        }
        $kept[] = $bid;
        $this->bids[$bid->loadId] = $kept;

        return true;
    }

    /**
     * The running order. Lowest amount first, then the shorter transit, then the
     * carrier name so the order never depends on insertion.
     *
     * @return list<Bid>
     */
    public function ranking(string $loadId): array
    {
        $list = $this->bids[$loadId] ?? [];
        usort($list, static function (Bid $a, Bid $b): int {
            return [$a->amountCents, $a->transitDays, $a->carrier]
                <=> [$b->amountCents, $b->transitDays, $b->carrier];
        });

        return $list;
    }

    public function award(string $loadId, string $carrier): bool
    {
        if (isset($this->awards[$loadId])) {
            return false;
        }
        foreach ($this->bids[$loadId] ?? [] as $bid) {
            if ($bid->carrier === $carrier) {
                $this->awards[$loadId] = $carrier;

                return true;
            }
        }

        return false;
    }

    public function awardedTo(string $loadId): ?string
    {
        return $this->awards[$loadId] ?? null;
    }

    /**
     * What the shipper saves against the ceiling once the load closes. A load
     * still running has no saving yet.
     */
    public function savingCents(string $loadId): int
    {
        $carrier = $this->awards[$loadId] ?? null;
        if ($carrier === null) {
            return 0;
        }
        foreach ($this->bids[$loadId] as $bid) {
            if ($bid->carrier === $carrier) {
                return $this->load($loadId)->ceilingCents - $bid->amountCents;
            }
        }

        return 0;
    }

    /** @return list<Load> */
    public function open(): array
    {
        $out = [];
        foreach ($this->loads as $id => $load) {
            if (!isset($this->awards[$id])) {
                $out[] = $load;
            }
        }

        return $out;
    }

    public function bidCount(string $loadId): int
    {
        return count($this->bids[$loadId] ?? []);
    }
}
