<?php

declare(strict_types=1);

namespace App\Tests\Unit\Docker;

use App\Demo\Controller\WebhookReceiverController;
use App\Demo\Webhook\DemoWebhookTarget;
use PHPUnit\Framework\TestCase;

/**
 * US-049: the body ceiling of the public webhook receiver is in docker/Caddyfile. No Caddy binary runs here: this keeps
 * the file aligned with the controller; `frankenphp validate` and bin/check-caddy-body-limit (CI job "Docker image")
 * prove that the file is valid and that the ceiling cuts the body, chunked included.
 */
final class CaddyfileTest extends TestCase
{
    private string $caddyfile;

    protected function setUp(): void
    {
        $this->caddyfile = (string) file_get_contents(\dirname(__DIR__, 3) . '/docker/Caddyfile');
    }

    public function testTheCeilingIsLimitedToTheReceiverPath(): void
    {
        self::assertMatchesRegularExpression(
            '#@(\w+) path ' . preg_quote(DemoWebhookTarget::PATH, '#') . '\s*\n\s*request_body @\1 \{#',
            $this->caddyfile,
            'request_body must be scoped by a matcher on the exact path of the receiver.',
        );
    }

    public function testTheCeilingLeavesRoomForTheOneByteThatLetsTheControllerAnswer413(): void
    {
        $controllerLimit = new \ReflectionClassConstant(WebhookReceiverController::class, 'MAX_BODY_BYTES')->getValue();
        self::assertIsInt($controllerLimit);

        preg_match('#request_body @\w+ \{\s*max_size (\d+)\s*\}#', $this->caddyfile, $match);
        self::assertSame($controllerLimit + 1, (int) ($match[1] ?? 0), 'Caddy cuts the stream: one byte over the limit of the controller makes its own 413 test fire.');
    }

    public function testTheCrossOriginHeadersAreSetOnEveryResponse(): void
    {
        self::assertStringContainsString('Cross-Origin-Opener-Policy "same-origin"', $this->caddyfile);
        self::assertStringContainsString('Cross-Origin-Resource-Policy "same-origin"', $this->caddyfile);
    }

    public function testHiddenFilesAreAnsweredNotFoundBeforePhpServerSeesThem(): void
    {
        self::assertMatchesRegularExpression('#@hidden \{\s*path_regexp hidden \(\^\|/\)\\\.\s*not path /\.well-known/\*\s*\}\s*respond @hidden 404#', $this->caddyfile);
        self::assertLessThan(strpos($this->caddyfile, 'php_server'), strpos($this->caddyfile, 'respond @hidden 404'));
    }

    public function testHstsIsOnlyAddedWhenTheApplicationDidNotSendItAlready(): void
    {
        // US-162: "?" makes Caddy set the header after the response is built, only if absent (otherwise it was sent twice).
        self::assertMatchesRegularExpression('#header @https \?Strict-Transport-Security "max-age=31536000; includeSubDomains"#', $this->caddyfile);
    }
}
