<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Analytics;

use App\Demo\Analytics\CampaignLabel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-117: a campaign is a plain label or it is ignored. */
final class CampaignLabelTest extends TestCase
{
    public function testAPlainCampaignIsKeptInLowerCase(): void
    {
        self::assertSame('ouverture-demo', CampaignLabel::clean(' Ouverture-Demo '));
    }

    /** @return iterable<string, array{mixed}> */
    public static function ignored(): iterable
    {
        yield 'too long' => [str_repeat('a', 41)];
        yield 'with spaces' => ['ouverture demo'];
        yield 'an e-mail address' => ['claire@acme.example'];
        yield 'markup' => ['"><script>'];
        yield 'empty' => [''];
        yield 'an array' => [['a']];
        yield 'null' => [null];
    }

    #[DataProvider('ignored')]
    public function testAnythingElseIsIgnored(mixed $value): void
    {
        self::assertNull(CampaignLabel::clean($value));
    }
}
