<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-186: the editorial home of the B2B channel (hero with a photo, commitments, aisles, services, roles, behind the
 * scenes), the demo notice under the header of both channels, the presentation links, and the B2C home left alone.
 */
final class HomeRedesignTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const ANONYMOUS_ORDER = ['hero', 'commitments', 'aisles', 'services', 'roles', 'backstage'];

    private const SIGNED_IN_ORDER = ['hero', 'commitments', 'usual', 'restock', 'negotiated', 'aisles', 'services', 'backstage'];

    /** The presentation links of the demo, each reachable from the demo menu and from the home. */
    private const PRESENTATION_LINKS = ['accounts', 'journeys', 'plugins', 'packs', 'integrate', 'mcp', 'architecture', 'proof', 'compatibility', 'override', 'about'];

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testTheSectionsFollowTheValidatedMockupForAVisitor(string $locale): void
    {
        $home = $this->anonymousHome($locale);

        self::assertSame(self::ANONYMOUS_ORDER, $this->sections($home));
        self::assertCount(1, $home->filter('h1'));
        self::assertCount(0, $home->filter('[data-home-promise], [data-b2b-home="quick-access"]'), 'the old blocks are gone');
    }

    #[DataProvider('locales')]
    public function testTheHeroHasAPhotoATitleAndTheTwoButtons(string $locale): void
    {
        $hero = $this->anonymousHome($locale)->filter('[data-home-section="hero"]');

        self::assertCount(1, $hero->filter('h1'));
        self::assertCount(1, $hero->filter('a.btn-primary[data-home-primary]'));
        self::assertCount(1, $hero->filter('a[data-home-role-choice][href="/demo/accounts"]'));
        $photo = $hero->filter('img[data-home-photo="hero"]');
        self::assertCount(1, $photo);
        self::assertNotSame('', trim((string) $photo->attr('alt')), 'the hero photo has a text alternative');
        self::assertSame('high', $photo->attr('fetchpriority'));
        self::assertNull($photo->attr('loading'), 'the hero is never lazy-loaded');
        self::assertNotNull($photo->attr('srcset'));
        self::assertNotNull($photo->attr('sizes'));
        self::assertGreaterThan(0, (int) $photo->attr('width'));
        self::assertGreaterThan(0, (int) $photo->attr('height'));
        self::assertCount(1, $hero->filter('[data-home-author-line]'));
    }

    #[DataProvider('locales')]
    public function testTheHeroPhotoIsPreloaded(string $locale): void
    {
        $home = $this->anonymousHome($locale);

        self::assertCount(1, $home->filter('link[rel="preload"][as="image"][imagesrcset]'));
    }

    #[DataProvider('locales')]
    public function testEveryPhotoOfTheHomeHasDimensionsAResponsiveSetAndAnAlt(string $locale): void
    {
        $photos = $this->anonymousHome($locale)->filter('main img[data-home-photo]');

        self::assertGreaterThanOrEqual(8, $photos->count(), 'hero, three aisles, services, three roles');
        foreach ($photos as $node) {
            $photo = new Crawler($node);
            $slot = (string) $photo->attr('data-home-photo');
            self::assertNotNull($photo->attr('alt'), $slot . ': alt attribute');
            self::assertGreaterThan(0, (int) $photo->attr('width'), $slot . ': width');
            self::assertGreaterThan(0, (int) $photo->attr('height'), $slot . ': height');
            self::assertMatchesRegularExpression('/\.webp \d+w/', (string) $photo->attr('srcset'), $slot . ': srcset in WebP');
            self::assertNotNull($photo->attr('sizes'), $slot . ': sizes');
            if ('hero' !== $slot) {
                self::assertSame('lazy', $photo->attr('loading'), $slot . ': lazy below the first view');
            }
            if ('' === $photo->attr('alt')) {
                self::assertNotNull($photo->attr('data-decorative'), $slot . ': an empty alt is a decision, written down');
            }
        }
    }

    public function testASignedInBuyerSeesTheHeroWithoutTheVisitorBlocksAndKeepsHerThreeProductBlocks(): void
    {
        $home = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertSame(self::SIGNED_IN_ORDER, $this->sections($home));
        self::assertCount(0, $home->filter('[data-b2b-home="roles"]'));
        self::assertCount(1, $home->filter('[data-home-section="hero"] a.btn-primary[href="/fr_FR/guides"]'));
        self::assertCount(0, $home->filter('[data-home-section="hero"] [data-home-role-choice]'));
        self::assertStringContainsString('Changer de compte de démo', $home->filter('[data-home-section="hero"] a.btn-outline-primary')->text());
    }

    public function testTheConsumerChannelKeepsItsStockHomeWithoutPhotoNorPresentationLink(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');
        $home = $client->request('GET', '/fr_FR/');
        $html = (string) $client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertCount(1, $home->filter('[aria-labelledby="new-collection"]'), 'the core home is untouched');
        self::assertCount(0, $home->filter('[data-home-section], [data-home-photo], [data-b2b-home]'));
        self::assertStringNotContainsString('images/home/', $html);
        self::assertCount(0, $home->filter('main a[data-demo-link]'), 'no presentation link in the page of the consumer channel');
    }

    #[DataProvider('locales')]
    public function testTheDemoNoticeSitsUnderTheHeaderOfTheHome(string $locale): void
    {
        $notice = $this->anonymousHome($locale)->filter('[data-demo-notice]');

        self::assertCount(1, $notice);
        self::assertSame('note', $notice->attr('role'));
        $text = trim((string) preg_replace('/\s+/', ' ', $notice->text()));
        self::assertStringContainsString('fr_FR' === $locale ? 'E-boutique de démonstration B2B' : 'B2B demonstration e-shop', $text);
        self::assertCount(1, $notice->filter('a[data-demo-link="about"]'));
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function noticePages(): iterable
    {
        foreach (['B2B', 'B2C'] as $channel) {
            foreach (['/fr_FR/', '/fr_FR/login', '/fr_FR/plugins', '/fr_FR/taxons/quincaillerie', '/fr_FR/products/outi-001-fr', '/en_US/'] as $path) {
                yield $channel . ' ' . $path => [$channel, $path, str_starts_with($path, '/en_US') ? 'B2B demonstration e-shop' : 'E-boutique de démonstration B2B'];
            }
        }
    }

    #[DataProvider('noticePages')]
    public function testTheNoticeIsOnEveryPageOfBothChannels(string $channel, string $path, string $text): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/' . $channel);
        $page = $client->request('GET', $path);

        self::assertResponseIsSuccessful($path);
        self::assertCount(1, $page->filter('[data-demo-notice]'), $channel . ' ' . $path);
        self::assertCount(1, $page->filter('header [data-demo-notice]'), 'inside the single header landmark');
        $html = (string) $client->getResponse()->getContent();
        self::assertGreaterThan(strpos($html, 'data-demo-banner'), strpos($html, 'data-demo-notice'), 'the notice comes after the demo bar');
        self::assertStringContainsString($text, trim((string) preg_replace('/\s+/', ' ', $page->filter('[data-demo-notice]')->text())));
    }

    public function testTheDemoMenuIsAnAccessiblePanelOfFourColumns(): void
    {
        $bar = $this->anonymousHome('fr_FR')->filter('[data-demo-banner]');

        self::assertCount(2, $bar->filter('details'), 'quick login and demo menu');
        $menu = $bar->filter('details[data-demo-menu="all"]');
        self::assertCount(1, $menu);
        self::assertCount(1, $menu->filter('summary'));
        self::assertStringContainsString('Tout le menu démo', $menu->filter('summary')->text());
        $columns = $menu->filter('[data-demo-menu-column]');
        self::assertSame(['try', 'understand', 'integrate', 'author'], $columns->each(static fn (Crawler $column): string => (string) $column->attr('data-demo-menu-column')));
        foreach ($columns as $column) {
            self::assertCount(1, (new Crawler($column))->filter('h2, h3, strong'), 'a heading per column');
        }
        foreach (self::PRESENTATION_LINKS as $link) {
            self::assertGreaterThanOrEqual(1, $menu->filter('a[data-demo-link="' . $link . '"]')->count(), $link . ' is in the menu');
        }
        self::assertCount(0, $menu->filter('[hidden], [aria-hidden="true"]'), 'the panel is plain markup: it works without JavaScript');
    }

    public function testTheBarShowsTheQuickLinksFromTheXlWidthOnly(): void
    {
        $quick = $this->anonymousHome('fr_FR')->filter('[data-demo-banner] [data-demo-quick-links] a[data-demo-link]');

        self::assertGreaterThanOrEqual(4, $quick->count());
        foreach ($quick as $link) {
            $classes = preg_split('/\s+/', trim((string) (new Crawler($link))->attr('class')));
            self::assertIsArray($classes);
            self::assertContains('d-none', $classes);
            self::assertContains('d-xl-inline-flex', $classes, 'the bar stays on one line below the xl width');
        }
    }

    #[DataProvider('locales')]
    public function testEveryPresentationLinkAnswersOk(string $locale): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $home = $client->request('GET', '/' . $locale . '/');

        $seen = [];
        foreach ($home->filter('a[data-demo-link]') as $node) {
            $link = new Crawler($node);
            $seen[(string) $link->attr('data-demo-link')][] = (string) $link->attr('href');
        }
        foreach (self::PRESENTATION_LINKS as $key) {
            self::assertArrayHasKey($key, $seen, $key . ' is reachable from the home');
        }
        $client->followRedirects(true);
        foreach (array_unique(array_merge(...array_values($seen))) as $href) {
            if (str_starts_with($href, 'https://') || str_starts_with($href, '#')) {
                continue;
            }
            $client->request('GET', $href);
            self::assertResponseIsSuccessful($href);
        }
    }

    public function testTheHomeLinksTheBackstageAndTheAuthorBlock(): void
    {
        $this->setProfile();
        $section = $this->anonymousHome('fr_FR')->filter('[data-home-section="backstage"]');

        self::assertSame('coulisses', $section->attr('id'));
        self::assertSame(['plugins', 'architecture', 'proof', 'integrate'], $section->filter('a[data-home-backstage]')->each(static fn (Crawler $card): string => (string) $card->attr('data-home-backstage')));
        self::assertCount(1, $section->filter('[data-home-author] a[data-author-repository]'));
        self::assertCount(1, $section->filter('[data-home-author] a[data-author-profile]'));
    }

    public function testTheThreeAislesLinkTheCategories(): void
    {
        $home = $this->anonymousHome('fr_FR');

        self::assertSame(['epi', 'quincaillerie', 'fournitures-industrielles'], $home->filter('[data-home-section="aisles"] a[data-home-aisle]')->each(static fn (Crawler $tile): string => (string) $tile->attr('data-home-aisle')));
        foreach ($home->filter('a[data-home-aisle]') as $tile) {
            self::assertMatchesRegularExpression('#^/fr_FR/taxons/#', (string) (new Crawler($tile))->attr('href'));
        }
    }

    public function testTheThreeRolesLeadToTheDemoAccountsWithoutAnyPrice(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $home = $client->request('GET', '/fr_FR/');

        $roles = $home->filter('[data-b2b-home="roles"]');
        self::assertSame(['buyer', 'approver', 'admin'], $roles->filter('[data-home-role]')->each(static fn (Crawler $role): string => (string) $role->attr('data-home-role')));
        self::assertGreaterThanOrEqual(3, $roles->filter('a[href="/demo/accounts"]')->count());
        self::assertDoesNotMatchRegularExpression('/\d+,\d{2}\s?€/u', (string) $client->getResponse()->getContent());
    }

    public function testTheCommitmentsComeFromTheShopAndNothingIsInvented(): void
    {
        $band = $this->anonymousHome('fr_FR')->filter('[data-home-section="commitments"]');

        self::assertCount(4, $band->filter('li'));
        $text = $band->text();
        foreach (['Facturation mensuelle', 'Paiement à terme sur compte', 'Support pro du lundi au vendredi, 8h-18h', 'Factur-X'] as $commitment) {
            self::assertStringContainsString($commitment, $text);
        }
    }

    public function testTheBackstageTitleUsesThePluginCountOfTheCatalogue(): void
    {
        $home = $this->anonymousHome('fr_FR');

        $count = (int) $home->filter('[data-home-author-line]')->attr('data-plugin-count');
        self::assertGreaterThan(0, $count);
        self::assertStringContainsString((string) $count, $home->filter('[data-home-section="backstage"] h2')->first()->text());
    }

    /** @return iterable<string, array{string, list<string>}> */
    public static function aisleLabels(): iterable
    {
        yield 'French' => ['fr_FR', ['EPI', 'Quincaillerie', 'Fournitures industrielles']];
        yield 'English' => ['en_US', ['PPE', 'Hardware', 'Industrial supplies']];
    }

    /** @param list<string> $labels */
    #[DataProvider('aisleLabels')]
    public function testTheHeaderMenuHasTheThreeAislesAndTheServices(string $locale, array $labels): void
    {
        $nav = $this->anonymousHome($locale)->filter('nav.b2b-nav');

        self::assertCount(1, $nav);
        $desktop = $nav->filter('[data-nav-list="desktop"]');
        self::assertSame(['epi', 'quincaillerie', 'fournitures-industrielles'], $desktop->filter('a[data-nav-aisle]')->each(static fn (Crawler $link): string => (string) $link->attr('data-nav-aisle')));
        self::assertSame($labels, $desktop->filter('a[data-nav-aisle]')->each(static fn (Crawler $link): string => trim($link->text())));
        foreach ($desktop->filter('a[data-nav-aisle]') as $link) {
            self::assertMatchesRegularExpression('#^/' . $locale . '/taxons/#', (string) (new Crawler($link))->attr('href'));
        }
        self::assertCount(1, $desktop->filter('a[href*="quick-order"]'));
        self::assertCount(1, $desktop->filter('a[href*="quote"]'));
        $classes = preg_split('/\s+/', trim((string) $desktop->attr('class')));
        self::assertIsArray($classes);
        self::assertContains('d-none', $classes, 'the row is hidden on a phone');
        self::assertContains('d-md-flex', $classes);
    }

    public function testBelowTheMdWidthTheMenuFoldsIntoANativeDisclosureWithTheSameLinks(): void
    {
        $nav = $this->anonymousHome('fr_FR')->filter('nav.b2b-nav');

        $mobile = $nav->filter('details[data-nav-list="mobile"]');
        self::assertCount(1, $mobile);
        self::assertContains('d-md-none', preg_split('/\s+/', trim((string) $mobile->attr('class'))) ?: []);
        self::assertCount(1, $mobile->filter('summary'));
        self::assertSame(
            $nav->filter('[data-nav-list="desktop"] a')->each(static fn (Crawler $link): string => (string) $link->attr('href')),
            $mobile->filter('a')->each(static fn (Crawler $link): string => (string) $link->attr('href')),
        );
    }

    #[DataProvider('locales')]
    public function testTheHeroCarriesNoRepeatedTagline(string $locale): void
    {
        $home = $this->anonymousHome($locale);
        $tagline = 'fr_FR' === $locale ? 'Interlocuteur dédié' : 'Dedicated account manager';

        self::assertCount(0, $home->filter('[data-home-section="hero"] .b2b-home-pill'));
        self::assertSame(1, substr_count($home->filter('body')->text(), $tagline), 'the tagline is in the top bar only');
    }

    public function testTheLogoMarkSpellsFP(): void
    {
        $mark = $this->anonymousHome('fr_FR')->filter('a.b2b-logo .b2b-logo__mark');

        self::assertCount(1, $mark);
        self::assertSame('FP', trim($mark->text()));
        self::assertSame('true', $mark->attr('aria-hidden'));
    }

    public function testTheMotionRespectsTheReducedMotionPreference(): void
    {
        $styles = (string) file_get_contents(\dirname(__DIR__, 3) . '/assets/shop/styles/_home.scss');

        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $styles);
        self::assertStringNotContainsString('autoplay', $styles);
    }

    private function setProfile(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => 'https://www.linkedin.com/in/thibautmonier/']);
    }

    private function anonymousHome(string $locale): Crawler
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');

        return $client->request('GET', '/' . $locale . '/');
    }

    /** @return list<string> */
    private function sections(Crawler $home): array
    {
        return $home->filter('main [data-home-section]')->each(static fn (Crawler $section): string => (string) $section->attr('data-home-section'));
    }

    private function signedIn(string $email): KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);
        $client->loginUser($user, 'shop');

        return $client;
    }
}
