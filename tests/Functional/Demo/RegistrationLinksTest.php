<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-162: no "create an account" link leads to a registration the demo refuses (403), in either language. */
final class RegistrationLinksTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const LINKS = 'a[href$="/register"], a[href*="/register/"]';

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR'];
        yield 'en' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testAClosedRegistrationShowsNoLinkOnTheHomeNorTheLoginPage(string $locale): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '0']);
        $client = self::createClient();

        foreach (['/', '/login', '/forgotten-password'] as $path) {
            $page = $client->request('GET', '/' . $locale . $path);

            self::assertResponseIsSuccessful();
            self::assertCount(0, $page->filter(self::LINKS), 'No registration link on ' . $path);
        }
    }

    #[DataProvider('locales')]
    public function testAnOpenRegistrationKeepsTheLinks(string $locale): void
    {
        $client = self::createClient();

        $home = $client->request('GET', '/' . $locale . '/');
        $login = $client->request('GET', '/' . $locale . '/login');

        self::assertGreaterThan(0, $home->filter(self::LINKS)->count(), 'Header link on the home page');
        self::assertGreaterThan(0, $login->filter(self::LINKS)->count(), 'Register box on the login page');
    }
}
