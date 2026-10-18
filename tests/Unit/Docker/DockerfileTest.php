<?php

declare(strict_types=1);

namespace App\Tests\Unit\Docker;

use PHPUnit\Framework\TestCase;

/** US-082 / US-081: Composer is a build tool, not part of the image that runs. */
final class DockerfileTest extends TestCase
{
    public function testTheFinalStageDoesNotShipComposer(): void
    {
        $dockerfile = (string) file_get_contents(\dirname(__DIR__, 3) . '/docker/Dockerfile');
        $final = substr($dockerfile, (int) strpos($dockerfile, 'FROM base AS final'));

        self::assertNotSame('', $final);
        self::assertStringNotContainsString('composer', strtolower($final));
        self::assertStringNotContainsString('COMPOSER', $final);
    }

    public function testComposerIsInstalledOnlyInTheBuildStages(): void
    {
        $dockerfile = (string) file_get_contents(\dirname(__DIR__, 3) . '/docker/Dockerfile');
        $beforeVendor = substr($dockerfile, 0, (int) strpos($dockerfile, 'FROM base AS vendor'));

        self::assertStringNotContainsString('/usr/bin/composer', $beforeVendor, 'The base stage is shared with the final image.');
        self::assertMatchesRegularExpression('/FROM base AS vendor\nCOPY --from=composer:[\d.]+@sha256:[0-9a-f]{64} \/usr\/bin\/composer/', $dockerfile);
    }
}
