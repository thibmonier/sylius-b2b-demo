<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-144: the top bar of the shop no longer advertises a newsletter discount, on either channel. */
final class TopBarWithoutNewsletterTest extends WebTestCase
{
    use EnvironmentSwitch;

    /** @return iterable<string, array{string, string}> */
    public static function channelsAndLocales(): iterable
    {
        yield 'B2B, French' => ['B2B', 'fr_FR'];
        yield 'B2B, English' => ['B2B', 'en_US'];
        yield 'B2C, French' => ['B2C', 'fr_FR'];
        yield 'B2C, English' => ['B2C', 'en_US'];
    }

    #[DataProvider('channelsAndLocales')]
    public function testTheTopBarHasNoNewsletterOffer(string $channel, string $locale): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/' . $channel);
        $page = $client->request('GET', '/' . $locale . '/');

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsStringIgnoringCase('newsletter', $page->filter('header')->text());
        self::assertStringNotContainsString('-20', $page->filter('header')->text());
    }
}
