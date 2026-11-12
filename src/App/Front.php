<?php

declare(strict_types=1);

namespace Tramex\App;

use Tramex\Home\Sample;

/**
 * The front controller. It builds the sample, routes the path and renders. The
 * health route carries the numbers a probe needs to tell a working process from
 * a live one that has lost its data.
 */
final class Front
{
    public function __construct(private readonly string $base)
    {
    }

    public function run(): void
    {
        $router = new Router($this->base);
        $views = new Views($this->base);
        $sample = new Sample();
        $today = getenv('TRAMEX_TODAY') ?: '12 November 2026';

        $router->get('/', static fn (): array => [200, [
            'html' => $views->front($sample, $today),
        ]]);

        $router->get('/health', static fn (): array => [200, ['json' => [
            'ok' => true,
            'loads' => count($sample->board()->open()),
            'awards' => $sample->board()->awardedTo('TR-403') === null ? 0 : 1,
        ]]]);

        [$status, $body] = $router->handle(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
        );

        http_response_code($status);
        if (isset($body['json'])) {
            header('Content-Type: application/json');
            echo json_encode($body['json'], JSON_UNESCAPED_SLASHES), "\n";

            return;
        }
        if (isset($body['html'])) {
            header('Content-Type: text/html; charset=utf-8');
            echo $body['html'];

            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $views->notFound((string) ($body['path'] ?? '/'));
    }
}
