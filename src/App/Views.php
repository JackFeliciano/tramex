<?php

declare(strict_types=1);

namespace Tramex\App;

use Tramex\Home\Board;
use Tramex\Home\Sample;

final class Views
{
    public function __construct(private readonly string $base)
    {
    }

    private function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function money(int $cents): string
    {
        return 'R$ ' . number_format(intdiv($cents, 100), 0, '.', ',') . ',' . sprintf('%02d', $cents % 100);
    }

    public function header(string $title): string
    {
        $base = $this->h($this->base);

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$this->h($title)}</title>
<base href="{$base}">
<link rel="stylesheet" href="assets/site.css">
</head>
<body>
<main>
HTML;
    }

    public function footer(): string
    {
        return "</main>\n</body>\n</html>\n";
    }

    public function notFound(string $path): string
    {
        return $this->header('Not found')
            . '<h1>Not found</h1>' . "\n"
            . '<p class="stage">' . $this->h($path) . ' is not a route here.</p>' . "\n"
            . $this->footer();
    }

    public function front(Sample $sample, string $today): string
    {
        $board = $sample->board();
        $out = $this->header('Tramex');
        $out .= '<h1>Tramex</h1>' . "\n";
        $out .= '<p class="lede">One freight exchange. A shipper posts the load, carriers bid under a ceiling, both sides follow the delivery.</p>' . "\n";
        $out .= '<p class="stage">Board as of ' . $this->h($today) . '.</p>' . "\n";

        $out .= '<h2>Open loads</h2>' . "\n";
        $out .= '<div class="cards">' . "\n";
        foreach ($board->open() as $load) {
            $out .= '<article class="card">' . "\n";
            $out .= '<h3>' . $this->h($load->origin) . ' to ' . $this->h($load->destination) . '</h3>' . "\n";
            $out .= '<p>' . $this->h($load->shipper) . ' posts ' . number_format($load->weightKg, 0, '.', ',') . ' kg for ' . $this->h($load->pickupDay) . '.</p>' . "\n";
            $out .= '<dl>';
            $out .= '<dt>Load</dt><dd>' . $this->h($load->id) . '</dd>';
            $out .= '<dt>Ceiling</dt><dd>' . $this->money($load->ceilingCents) . '</dd>';
            $out .= '<dt>Bids</dt><dd>' . $board->bidCount($load->id) . '</dd>';
            $out .= '</dl>' . "\n";
            $out .= '</article>' . "\n";
        }
        $out .= '</div>' . "\n";

        $out .= $this->running($board, 'TR-401');
        $out .= $this->closed($board, 'TR-403');
        $out .= $this->footer();

        return $out;
    }

    private function running(Board $board, string $loadId): string
    {
        $load = $board->load($loadId);
        $out = '<h2>Bidding on ' . $this->h($load->id) . '</h2>' . "\n";
        $out .= '<p class="stage">' . $this->h($load->origin) . ' to ' . $this->h($load->destination) . ', ceiling ' . $this->money($load->ceilingCents) . '.</p>' . "\n";
        $out .= '<table class="tiers">' . "\n";
        $out .= '<thead><tr><th>Rank</th><th>Carrier</th><th>Offer</th><th>Transit</th><th>Under ceiling</th></tr></thead>' . "\n";
        $out .= '<tbody>' . "\n";
        foreach ($board->ranking($loadId) as $index => $bid) {
            $under = $load->ceilingCents - $bid->amountCents;
            $out .= '<tr><td>' . ($index + 1) . '</td>';
            $out .= '<td>' . $this->h($bid->carrier) . '</td>';
            $out .= '<td>' . $this->money($bid->amountCents) . '</td>';
            $out .= '<td>' . $bid->transitDays . ' days</td>';
            $out .= '<td>' . $this->money($under) . '</td></tr>' . "\n";
        }
        $out .= '</tbody>' . "\n" . '</table>' . "\n";

        return $out;
    }

    private function closed(Board $board, string $loadId): string
    {
        $load = $board->load($loadId);
        $carrier = $board->awardedTo($loadId);
        $out = '<h2>Closed load</h2>' . "\n";
        $out .= '<div class="cards"><article class="card">' . "\n";
        $out .= '<h3>' . $this->h($load->id) . ' ' . $this->h($load->origin) . ' to ' . $this->h($load->destination) . '</h3>' . "\n";
        $out .= '<p class="stage">' . ($carrier !== null ? 'Awarded to ' . $this->h($carrier) . '.' : 'Still open.') . '</p>' . "\n";
        $out .= '<dl>';
        $out .= '<dt>Ceiling</dt><dd>' . $this->money($load->ceilingCents) . '</dd>';
        $out .= '<dt>Saving</dt><dd>' . $this->money($board->savingCents($loadId)) . '</dd>';
        $out .= '<dt>Bids</dt><dd>' . $board->bidCount($loadId) . '</dd>';
        $out .= '</dl>' . "\n";
        $out .= '</article></div>' . "\n";

        return $out;
    }
}
