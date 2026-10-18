<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/** US-075: the demo accounts come first on the login page, grouped by company, one language, one click. */
final class LoginDemoAccountsTest extends WebTestCase
{
    use EnvironmentSwitch;

    /** @return iterable<string, array{string, string, string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'Acheteur', 'Buyer', 'Se connecter en tant que'];
        yield 'English' => ['en_US', 'Buyer', 'Acheteur', 'Log in as'];
    }

    #[DataProvider('locales')]
    public function testTheAccountsPrecedeTheClassicForm(string $locale, string $role, string $otherLanguage, string $verb): void
    {
        $html = $this->loginPage($locale)->html();

        $accounts = strpos($html, 'data-demo-accounts="shop"');
        $form = strpos($html, 'id="_username"');
        self::assertNotFalse($accounts);
        self::assertNotFalse($form);
        self::assertLessThan($form, $accounts, 'accounts first, classic form below');
    }

    #[DataProvider('locales')]
    public function testAccountsAreGroupedByCompanyWithTheirRole(string $locale, string $role, string $otherLanguage, string $verb): void
    {
        $groups = $this->loginPage($locale)->filter('[data-demo-accounts="shop"] [data-demo-company]');

        self::assertCount(6, $groups, '5 companies and the B2C shoppers');
        $acme = $groups->reduce(static fn (Crawler $group): bool => str_contains($group->filter('h3')->text(), 'Acme Industries'));
        self::assertCount(1, $acme);
        self::assertCount(3, $acme->filter('[data-demo-login]'), 'Claire, Paul, Isabelle');
        self::assertStringContainsString($role, $acme->filter('[data-demo-login="claire@acme.example"]')->text());
    }

    #[DataProvider('locales')]
    public function testOneLanguageAtATimeAndTheOneClickAnnouncesTheRole(string $locale, string $role, string $otherLanguage, string $verb): void
    {
        $list = $this->loginPage($locale)->filter('[data-demo-accounts="shop"]');
        $button = $list->filter('[data-demo-login="claire@acme.example"]');

        self::assertStringContainsString($verb, $button->text());
        self::assertStringContainsString('Claire', $button->text());
        self::assertStringNotContainsString($otherLanguage, $list->text(), 'no bilingual wording in the list');
        self::assertStringNotContainsString(' / ', $button->text());
    }

    public function testADisabledAccountHasNoOneClick(): void
    {
        $list = $this->loginPage('fr_FR')->filter('[data-demo-accounts="shop"]');

        self::assertCount(0, $list->filter('[data-demo-login="thomas@buildco.example"]'));
        self::assertStringContainsString('désactivé', $list->text());
    }

    public function testNoAccountOutsideTheDemoMode(): void
    {
        $this->setEnvironment(['DEMO_MODE' => '0']);

        $page = $this->loginPage('fr_FR');

        self::assertCount(0, $page->filter('[data-demo-accounts], [data-demo-login]'));
        self::assertCount(1, $page->filter('#_username'), 'the classic form stays');
    }

    private function loginPage(string $locale): Crawler
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $page = $client->request('GET', '/' . $locale . '/login');
        self::assertResponseIsSuccessful();

        return $page;
    }
}
