<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Compatibility\Refresh\DemoStack;
use App\Demo\Proof\Refresh\SourceUnavailable;
use PHPUnit\Framework\TestCase;

/** US-171: "tested together in the demo" is what the demo's own composer.lock installs, nothing declared by hand. */
final class DemoStackTest extends TestCase
{
    public function testTheLockGivesSyliusSymfonyPhpAndTheInstalledPlugins(): void
    {
        $stack = DemoStack::fromLock((string) json_encode([
            'packages' => [
                ['name' => 'sylius/sylius', 'version' => 'v2.3.2'],
                ['name' => 'symfony/framework-bundle', 'version' => 'v8.1.9'],
                ['name' => 'tmonier/sylius-quick-order-plugin', 'version' => 'v1.3.0'],
                ['name' => 'doctrine/orm', 'version' => '3.0.0'],
            ],
            'platform' => ['php' => '^8.5'],
        ]));

        self::assertSame('2.3.2', $stack['sylius']);
        self::assertSame('8.1.9', $stack['symfony']);
        self::assertSame('^8.5', $stack['php']);
        self::assertSame(['sylius-quick-order-plugin' => 'v1.3.0'], $stack['plugins']);
    }

    public function testALockWithoutSyliusOrUnreadableIsASourceFailure(): void
    {
        foreach ([(string) json_encode(['packages' => []]), '{ not json'] as $lock) {
            try {
                DemoStack::fromLock($lock);
                self::fail('a lock without Sylius must not give a stack');
            } catch (SourceUnavailable) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
