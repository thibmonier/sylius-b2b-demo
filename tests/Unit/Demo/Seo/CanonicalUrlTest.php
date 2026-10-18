<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Seo;

use App\Demo\Seo\CanonicalUrl;
use PHPUnit\Framework\TestCase;

/** US-078: the canonical address follows DEFAULT_URI, which follows CANONICAL_IS_PUBLIC_HOST. */
final class CanonicalUrlTest extends TestCase
{
    public function testThePathIsAppendedToTheConfiguredHost(): void
    {
        self::assertSame('https://sylius-b2b.example/fr_FR/plugins', (new CanonicalUrl('https://sylius-b2b.example'))->forPath('/fr_FR/plugins'));
    }

    public function testASlashEitherSideIsNotDoubled(): void
    {
        self::assertSame('https://sylius-b2b.example/fr_FR/', (new CanonicalUrl('https://sylius-b2b.example/'))->forPath('/fr_FR/'));
    }

    public function testThePageNumberOfAListingIsKeptFromTheSecondPage(): void
    {
        $canonical = new CanonicalUrl('https://sylius-b2b.example');

        self::assertSame('https://sylius-b2b.example/fr_FR/taxons/vis?page=3', $canonical->forPath('/fr_FR/taxons/vis', '3'));
        self::assertSame('https://sylius-b2b.example/fr_FR/taxons/vis', $canonical->forPath('/fr_FR/taxons/vis', '1'));
        self::assertSame('https://sylius-b2b.example/fr_FR/taxons/vis', $canonical->forPath('/fr_FR/taxons/vis', '2&x=<'));
    }
}
