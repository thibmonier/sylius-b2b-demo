<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Site;

use App\Demo\Site\SiteLinks;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-074: the site and contact URLs of the footer are accepted in https only, anything else is hidden. */
final class SiteLinksTest extends TestCase
{
    public function testAnHttpsUrlIsExposed(): void
    {
        $links = new SiteLinks('https://example.com/', 'https://example.com/contact?from=demo', 'https://exemple.test/profil');

        self::assertSame('https://example.com/', $links->siteUrl());
        self::assertSame('https://example.com/contact?from=demo', $links->contactUrl());
        self::assertSame('https://exemple.test/profil', $links->profileUrl());
    }

    public function testNoUrlMeansNoLinkAndNoError(): void
    {
        $links = new SiteLinks('', '   ', '');

        self::assertNull($links->siteUrl());
        self::assertNull($links->contactUrl());
    }

    /** @return iterable<string, array{string}> */
    public static function refusedUrls(): iterable
    {
        yield 'http' => ['http://example.com/'];
        yield 'javascript' => ['javascript:alert(1)'];
        yield 'data' => ['data:text/html,<script>alert(1)</script>'];
        yield 'relative' => ['/contact'];
        yield 'no host' => ['https://'];
        yield 'empty host with a path' => ['https:///x'];
        yield 'user without password' => ['https://user@example.com'];
        yield 'user without password, short host' => ['https://user@h'];
        yield 'user info' => ['https://user:secret@example.com/'];
        yield 'control character' => ["https://example.com/\r\nSet-Cookie: x=1"];
        yield 'not a url' => ['not a url'];
    }

    #[DataProvider('refusedUrls')]
    public function testAnythingButHttpsIsRefused(string $url): void
    {
        $links = new SiteLinks($url, $url, $url);

        self::assertNull($links->siteUrl());
        self::assertNull($links->contactUrl());
        self::assertNull($links->profileUrl());
    }
}
