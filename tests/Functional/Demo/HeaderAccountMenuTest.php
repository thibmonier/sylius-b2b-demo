<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-192: on a wide screen, a signed-in buyer keeps the cart, the favourites and a "Hello <name>!" button in the header;
 * the account links (My account, Approvals, Log out, whatever a plugin adds) are in the dropdown of that button. On a phone
 * the same dropdown is the icon button it always was (one menu in the page: the core entries carry fixed ids). A visitor does not change.
 */
final class HeaderAccountMenuTest extends WebTestCase
{
    use IsolatesSharedAccounts;

    /** @return iterable<string, array{string, string, string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR', 'Bonjour Claire!', 'Mon compte'];
        yield 'en' => ['en_US', 'Hello Claire!', 'My account'];
    }

    #[DataProvider('locales')]
    public function testASignedInBuyerHasAGreetingButtonThatOpensTheAccountMenu(string $locale, string $greeting, string $account): void
    {
        $page = $this->page('claire@acme.example', '/' . $locale . '/');

        $menu = $page->filter('header [data-b2b-account-menu]');
        self::assertCount(1, $menu);
        $button = $menu->filter('button[data-b2b-account-toggle]');
        self::assertCount(1, $button);
        self::assertSame('button', $button->attr('type'));
        self::assertSame('false', $button->attr('aria-expanded'));
        self::assertSame('dropdown', $button->attr('data-bs-toggle'));
        self::assertStringContainsString($greeting, trim((string) preg_replace('/\s+/', ' ', $button->filter('span.d-none.d-lg-inline')->text())), 'the visible text is the name of the button on a wide screen');
        $list = $menu->filter('ul.dropdown-menu');
        self::assertCount(1, $list);
        self::assertSame($list->attr('id'), $button->attr('aria-controls'));
        self::assertContains($account, $list->filter('a.dropdown-item')->each(static fn (Crawler $item): string => trim($item->text())));
    }

    public function testTheAccountLinksAreOnlyInTheMenuAndKeepTheirOrder(): void
    {
        $page = $this->page('paul@acme.example', '/fr_FR/');

        $menu = $page->filter('header [data-b2b-account-menu] ul.dropdown-menu');
        $hrefs = $menu->filter('a.dropdown-item')->each(static fn (Crawler $item): string => (string) $item->attr('href'));
        self::assertCount(3, $hrefs, 'My account, Approvals, Log out');
        self::assertStringContainsString('/account/', $hrefs[0]);
        self::assertStringContainsString('approvals', $hrefs[1]);
        self::assertStringContainsString('logout', $hrefs[2]);
        self::assertCount(1, $menu->filter('[data-test-tmonier-header-approvals="mobile"]'), 'the plugin entry is a hookable of the menu');
        self::assertCount(0, $page->filter('header [data-test-tmonier-header-approvals="desktop"]'), 'no entry left in the row');
        self::assertCount(1, $page->filter('header a[href$="/logout"]'), 'one menu for every width, none in the row');
    }

    public function testTheOldDesktopLinksAreNotInTheRowAnyMore(): void
    {
        $page = $this->page('claire@acme.example', '/fr_FR/');

        self::assertCount(0, $page->filter('header .d-lg-flex a[href*="logout"], header .d-lg-flex a[href*="account/dashboard"]'));
    }

    public function testOnAPhoneTheSameButtonIsTheIconWithItsAccessibleName(): void
    {
        $page = $this->page('claire@acme.example', '/fr_FR/');

        $button = $page->filter('header [data-b2b-account-menu] button[data-b2b-account-toggle]');
        self::assertCount(1, $button);
        self::assertSame('Mon compte', trim($button->filter('span.visually-hidden.d-lg-none')->text()), 'the name on a phone, where the greeting is hidden');
        self::assertCount(1, $page->filter('header #mobile-my-account-button'), 'no duplicate id: one menu in the page');
        self::assertCount(0, $page->filter('header .d-lg-none button[data-bs-toggle="dropdown"]'), 'no second account button for phones');
    }

    public function testAVisitorSeesTheSameHeaderAsBefore(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $page = $client->request('GET', '/fr_FR/');

        self::assertCount(0, $page->filter('[data-b2b-account-menu]'));
        self::assertGreaterThanOrEqual(1, $page->filter('header a[href$="/login"]')->count());
        self::assertGreaterThanOrEqual(1, $page->filter('header a[href$="/register"]')->count());
    }

    public function testTheConsumerChannelKeepsTheCoreAccountLinks(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $client->request('GET', '/demo/channel/B2C');
        $page = $client->request('GET', '/fr_FR/login');

        self::assertCount(0, $page->filter('[data-b2b-account-menu]'));
    }

    private function page(string $email, string $path): Crawler
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, $email);
        $page = $client->request('GET', $path);
        self::assertResponseIsSuccessful();

        return $page;
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);
        $client->loginUser($user, 'shop');
    }
}
