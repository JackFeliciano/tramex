<?php

declare(strict_types=1);

namespace Tramex\Tests;

use Tramex\Home\Bid;
use Tramex\Home\Board;
use Tramex\Home\Load;
use Tramex\Home\Sample;

return [
    ['a load needs a weight', static function (): void {
        $board = new Board();
        try {
            $board->post(new Load('X-1', 'Shipper', 'A', 'B', 0, 'day', 100000));
            Assert::true(false, 'a zero weight was accepted');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['a bid over the ceiling is refused', static function (): void {
        $board = new Board();
        $board->post(new Load('X-1', 'Shipper', 'A', 'B', 100, 'day', 100000));
        Assert::false($board->bid(new Bid('X-1', 'Carrier', 100001, 1)));
        Assert::same(0, $board->bidCount('X-1'));
    }],
    ['a bid at the ceiling enters', static function (): void {
        $board = new Board();
        $board->post(new Load('X-1', 'Shipper', 'A', 'B', 100, 'day', 100000));
        Assert::true($board->bid(new Bid('X-1', 'Carrier', 100000, 1)));
        Assert::same(1, $board->bidCount('X-1'));
    }],
    ['a carrier replaces its own bid', static function (): void {
        $board = new Board();
        $board->post(new Load('X-1', 'Shipper', 'A', 'B', 100, 'day', 100000));
        $board->bid(new Bid('X-1', 'Carrier', 90000, 2));
        $board->bid(new Bid('X-1', 'Carrier', 88000, 2));
        Assert::same(1, $board->bidCount('X-1'));
        Assert::same(88000, $board->ranking('X-1')[0]->amountCents);
    }],
    ['ranking sorts by amount then transit then name', static function (): void {
        $board = new Board();
        $board->post(new Load('X-1', 'Shipper', 'A', 'B', 100, 'day', 100000));
        $board->bid(new Bid('X-1', 'Zulu', 90000, 3));
        $board->bid(new Bid('X-1', 'Alfa', 90000, 3));
        $board->bid(new Bid('X-1', 'Mike', 90000, 2));
        $names = array_map(static fn (Bid $b): string => $b->carrier, $board->ranking('X-1'));
        Assert::same(['Mike', 'Alfa', 'Zulu'], $names);
    }],
    ['an award closes the load and stops bids', static function (): void {
        $board = new Board();
        $board->post(new Load('X-1', 'Shipper', 'A', 'B', 100, 'day', 100000));
        $board->bid(new Bid('X-1', 'Carrier', 90000, 2));
        Assert::true($board->award('X-1', 'Carrier'));
        Assert::same('Carrier', $board->awardedTo('X-1'));
        Assert::false($board->bid(new Bid('X-1', 'Other', 80000, 1)));
        Assert::same(1, $board->bidCount('X-1'));
    }],
    ['an award to a carrier without a bid fails', static function (): void {
        $board = new Board();
        $board->post(new Load('X-1', 'Shipper', 'A', 'B', 100, 'day', 100000));
        Assert::false($board->award('X-1', 'Nobody'));
        Assert::same(null, $board->awardedTo('X-1'));
    }],
    ['a second award on one load fails', static function (): void {
        $board = new Board();
        $board->post(new Load('X-1', 'Shipper', 'A', 'B', 100, 'day', 100000));
        $board->bid(new Bid('X-1', 'One', 90000, 2));
        $board->bid(new Bid('X-1', 'Two', 95000, 2));
        $board->award('X-1', 'One');
        Assert::false($board->award('X-1', 'Two'));
        Assert::same('One', $board->awardedTo('X-1'));
    }],
    ['the saving is the ceiling less the winning bid', static function (): void {
        $board = new Board();
        $board->post(new Load('X-1', 'Shipper', 'A', 'B', 100, 'day', 100000));
        $board->bid(new Bid('X-1', 'Carrier', 91000, 2));
        Assert::same(0, $board->savingCents('X-1'));
        $board->award('X-1', 'Carrier');
        Assert::same(9000, $board->savingCents('X-1'));
    }],
    ['the sample board has three open loads and one closed', static function (): void {
        $board = Sample::board();
        Assert::count(3, $board->open());
        Assert::same('Pampa Frete', $board->awardedTo('TR-403'));
        Assert::same(25000, $board->savingCents('TR-403'));
    }],
];
