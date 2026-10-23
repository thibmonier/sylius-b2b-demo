<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Compatibility\Refresh\ComposerLinks;
use App\Demo\Proof\Refresh\SourceUnavailable;
use PHPUnit\Framework\TestCase;

/** US-171: a mandatory dependency (require) is told apart from a suggestion (suggest), by plugin slug. */
final class ComposerLinksTest extends TestCase
{
    public function testTradeCreditRequiresOrderHoldAndOnlySuggestsFacturx(): void
    {
        $links = ComposerLinks::parse((string) json_encode([
            'require' => ['php' => '^8.2', 'sylius/sylius' => '^2.3', 'tmonier/sylius-order-hold-plugin' => '^1.0'],
            'require-dev' => ['tmonier/sylius-dev-only-plugin' => '^1.0'],
            'suggest' => ['tmonier/sylius-facturx-plugin' => 'Due date in invoices', 'sylius/invoicing-plugin' => 'Invoice links'],
        ]));

        self::assertSame(['sylius-order-hold-plugin'], $links['requires']);
        self::assertSame(['sylius-facturx-plugin'], $links['suggests']);
    }

    public function testASectionThatIsAbsentIsEmptyAndSlugsAreSorted(): void
    {
        $links = ComposerLinks::parse((string) json_encode(['suggest' => ['tmonier/sylius-b-plugin' => 'x', 'tmonier/sylius-a-plugin' => 'y']]));

        self::assertSame([], $links['requires']);
        self::assertSame(['sylius-a-plugin', 'sylius-b-plugin'], $links['suggests']);
    }

    public function testAnUnreadableComposerJsonIsASourceFailure(): void
    {
        $this->expectException(SourceUnavailable::class);

        ComposerLinks::parse('{ not json');
    }
}
