<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mcp;

use App\Demo\Mcp\McpServerUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * US-103: the address of the MCP server shown to an assistant is the one of the public site, never localhost. Locally (or
 * when DEFAULT_URI is not a public https address) the address of the request is kept.
 */
final class McpServerUrlTest extends TestCase
{
    /** @return iterable<string, array{string, string, string}> */
    public static function publicSites(): iterable
    {
        yield 'seen through localhost' => ['https://sylius-b2b.thibaut-monier.fr', 'http://localhost/_mcp', 'https://sylius-b2b.thibaut-monier.fr/_mcp'];
        yield 'seen through the platform domain' => ['https://sylius-b2b.thibaut-monier.fr', 'https://demo.up.railway.app/_mcp', 'https://sylius-b2b.thibaut-monier.fr/_mcp'];
        yield 'trailing slash' => ['https://sylius-b2b.thibaut-monier.fr/', 'http://127.0.0.1:8123/_mcp', 'https://sylius-b2b.thibaut-monier.fr/_mcp'];
    }

    #[DataProvider('publicSites')]
    public function testThePublicSiteWinsOverTheHostOfTheRequest(string $defaultUri, string $requestUrl, string $expected): void
    {
        self::assertSame($expected, (new McpServerUrl($defaultUri))->forRequest($requestUrl));
    }

    /** @return iterable<string, array{string}> */
    public static function nonPublicDefaults(): iterable
    {
        yield 'local development' => ['http://localhost:8080'];
        yield 'loopback address' => ['http://127.0.0.1:8080'];
        yield 'https on localhost' => ['https://localhost'];
        yield 'plain http, even on a public host' => ['http://sylius-b2b.thibaut-monier.fr'];
        yield 'empty' => [''];
        yield 'not an URL' => ['not a url'];
    }

    #[DataProvider('nonPublicDefaults')]
    public function testWithoutAPublicDefaultTheAddressOfTheRequestIsKept(string $defaultUri): void
    {
        self::assertSame('http://localhost/_mcp', (new McpServerUrl($defaultUri))->forRequest('http://localhost/_mcp'));
    }
}
