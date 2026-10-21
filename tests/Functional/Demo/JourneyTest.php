<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Command\ResetStatus;
use App\Demo\Journey\JourneyCatalog;
use App\Demo\Journey\JourneyLauncher;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-105 / US-106 / US-117: the guided journeys, in both languages. Run against a database loaded with the `demo` suite.
 * A test that prepares a scenario changes the cart of a shared demo account: it runs inside a transaction that is rolled back.
 */
final class JourneyTest extends WebTestCase
{
    use DatabaseAccess;
    use EnvironmentSwitch;

    private const CLAIRE = 'claire@acme.example';

    private const CONTACT = 'https://exemple.test/contact';

    private ?Connection $transaction = null;

    protected function tearDown(): void
    {
        if (null !== $this->transaction && $this->transaction->isTransactionActive()) {
            $this->transaction->rollBack();
        }
        $this->transaction = null;
        parent::tearDown();
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR', 'Étape 1 sur 4', 'Reprendre'];
        yield 'en' => ['en_US', 'Step 1 of 4', 'Resume'];
    }

    #[DataProvider('locales')]
    public function testTheListShowsTheFiveJourneysWithTheirDuration(string $locale): void
    {
        $client = self::createClient();

        $page = $client->request('GET', '/' . $locale . '/guides');

        self::assertResponseIsSuccessful();
        self::assertCount(5, $page->filter('[data-journey-list] > li'));
        self::assertCount(5, $page->filter('[data-journey-duration]'));
        self::assertMatchesRegularExpression('/\d+ min/', $page->filter('[data-journey-duration]')->first()->text());
        self::assertCount(5, $page->filter('a[data-journey-start]'));
        self::assertStringNotContainsString('journey.', $page->filter('main, body')->first()->text(), 'No raw translation key.');
    }

    #[DataProvider('locales')]
    public function testAJourneyIsFollowedStepByStepWithAVisibleAndAnnouncedProgress(string $locale, string $counter): void
    {
        $client = self::createClient();
        $this->login($client, self::CLAIRE);

        $step1 = $client->request('GET', '/' . $locale . '/guides/buyer/1');
        self::assertResponseIsSuccessful();
        self::assertSame($counter, trim($step1->filter('[data-journey-counter]')->text()));
        self::assertSame('status', $step1->filter('[data-journey-counter]')->attr('role'));
        self::assertSame('polite', $step1->filter('[data-journey-counter]')->attr('aria-live'));
        self::assertSame('progressbar', $step1->filter('[data-journey-progress]')->attr('role'));
        self::assertCount(4, $step1->filter('[data-journey-steps] li'));
        self::assertSame('step', $step1->filter('[data-journey-steps] a[aria-current]')->attr('aria-current'));
        self::assertCount(0, $step1->filter('[data-journey-previous]'), 'No previous step before the first.');
        self::assertCount(1, $step1->filter('[data-journey-open]'));

        $client->submit($step1->filter('[data-journey-next]')->form(), [], ['HTTP_ORIGIN' => 'http://localhost']);
        $step2 = $client->followRedirect();
        self::assertSame('/' . $locale . '/guides/buyer/2', parse_url($client->getRequest()->getUri(), \PHP_URL_PATH));
        self::assertCount(1, $step2->filter('[data-journey-previous]'));
        self::assertCount(0, $step2->filter('[data-journey-launch]'), 'Step 2 has no scenario.');

        $back = $client->click($step2->filter('[data-journey-previous]')->link());
        self::assertSame('/' . $locale . '/guides/buyer/1', parse_url($client->getRequest()->getUri(), \PHP_URL_PATH));
        self::assertCount(1, $back->filter('[data-journey-next]'));
    }

    public function testTheLastStepLeadsToTheExitScreen(): void
    {
        $client = self::createClient();
        $this->login($client, self::CLAIRE);

        $step4 = $client->request('GET', '/fr_FR/guides/buyer/4');
        self::assertStringContainsString('Terminer le parcours', $step4->filter('[data-journey-next]')->text());

        $client->submit($step4->filter('[data-journey-next]')->form(), [], ['HTTP_ORIGIN' => 'http://localhost']);
        $exit = $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertCount(1, $exit->filter('[data-journey-exit]'));
        self::assertStringContainsString('Parcours terminé', $exit->filter('[data-journey-counter]')->text());
        self::assertCount(0, $exit->filter('[data-journey-next]'));
    }

    public function testTheExitScreenOffersTheContactOnlyWhenAUrlIsConfigured(): void
    {
        $this->setEnvironment(['DEMO_CONTACT_URL' => self::CONTACT]);
        $client = self::createClient();
        $with = $client->request('GET', '/fr_FR/guides/buyer/5');
        $contact = $with->filter('[data-journey-contact]');
        self::assertCount(1, $contact);
        self::assertStringStartsWith(self::CONTACT . '?', (string) $contact->attr('href'));
        self::assertStringContainsString('utm_content=journey-exit', (string) $contact->attr('href'));
        self::assertStringContainsString('Écrire un message', $contact->text());
        self::assertSame('/fr_FR/integrer', $with->filter('[data-journey-integrate]')->attr('href'), 'The exit screen links to the page "Intégrer".');

        $this->setEnvironment(['DEMO_CONTACT_URL' => '']);
        self::ensureKernelShutdown();
        $without = self::createClient()->request('GET', '/fr_FR/guides/buyer/5');

        self::assertCount(0, $without->filter('[data-journey-contact]'));
        self::assertCount(1, $without->filter('[data-journey-exit]'), 'The exit screen stays, with its internal link only.');
        self::assertCount(1, $without->filter('[data-journey-integrate]'));
    }

    public function testAnUnknownJourneyOrStepIsNotFound(): void
    {
        $client = self::createClient();

        foreach (['/fr_FR/guides/nope/1', '/fr_FR/guides/buyer/0', '/fr_FR/guides/buyer/6'] as $path) {
            $client->request('GET', $path);
            self::assertResponseStatusCodeSame(404, $path);
        }
    }

    #[DataProvider('locales')]
    public function testTheWrongAccountIsOfferedAOneClickSwitch(string $locale): void
    {
        $client = self::createClient();
        $this->login($client, 'marc@techparts.example');

        $page = $client->request('GET', '/' . $locale . '/guides/buyer/1');

        $alert = $page->filter('[data-journey-wrong-account]');
        self::assertCount(1, $alert);
        self::assertCount(1, $alert->filter('button[data-demo-login="' . self::CLAIRE . '"]'));
        self::assertCount(0, $page->filter('[data-journey-open], [data-journey-launch]'), 'Nothing runs with another account.');
    }

    public function testAVisitorIsOfferedTheSwitchToo(): void
    {
        $page = self::createClient()->request('GET', '/en_US/guides/approver/1');

        self::assertCount(1, $page->filter('[data-journey-wrong-account] button[data-demo-login="paul@acme.example"]'));
    }

    public function testLaunchingTheScenarioFillsTheCartAndLeadsToTheCart(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->beginTransaction();
        $this->login($client, self::CLAIRE);

        $crawler = $client->request('GET', '/fr_FR/guides/buyer/3');
        $launch = $crawler->filter('[data-journey-launch]');
        self::assertCount(1, $launch);
        $client->submit($launch->form());

        self::assertResponseRedirects('/fr_FR/cart/');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSame(5, $this->cartLines(), 'The five lines of the list "Atelier".');

        // Replayable: a second launch gives the same cart, not doubled quantities.
        $quantity = $this->cartQuantity();
        $crawler = $client->request('GET', '/fr_FR/guides/buyer/3');
        $client->submit($crawler->filter('[data-journey-launch]')->form());
        self::assertSame($quantity, $this->cartQuantity());
    }

    public function testLaunchingTheQuoteScenarioPreparesTheQuoteDraft(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->beginTransaction();
        $this->login($client, self::CLAIRE);

        $crawler = $client->request('GET', '/fr_FR/guides/buyer/4');
        $client->submit($crawler->filter('[data-journey-launch]')->form());

        self::assertResponseRedirects('/fr_FR/quote-request');
        $form = $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual(5, $form->filter('input[name*="quantity"]')->count(), 'The five lines of the list are in the draft.');
    }

    public function testLaunchingWithAnotherAccountChangesNothing(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->beginTransaction();
        $this->login($client, 'marc@techparts.example');
        $before = $this->cartLines();

        $client->request('POST', '/fr_FR/guides/buyer/3/launch', ['_token' => 'whatever']);

        self::assertResponseRedirects('/fr_FR/guides/buyer/3');
        self::assertSame($before, $this->cartLines());
    }

    public function testALaunchWithAnInvalidTokenIsRefused(): void
    {
        $client = self::createClient();
        $this->login($client, self::CLAIRE);

        $client->request('POST', '/fr_FR/guides/buyer/3/launch', ['_token' => 'nope']);

        self::assertResponseStatusCodeSame(403);
    }

    public function testAResetBringsTheVisitorBackToStepOneWithAMessage(): void
    {
        $client = self::createClient();
        $this->login($client, self::CLAIRE);
        $client->submit($client->request('GET', '/fr_FR/guides/buyer/2')->filter('[data-journey-next]')->form(), [], ['HTTP_ORIGIN' => 'http://localhost']);

        $markerFile = self::getContainer()->getParameter('demo.reset_marker_file');
        self::assertIsString($markerFile);
        new ResetStatus($markerFile)->markSucceeded();

        $client->request('GET', '/fr_FR/guides/buyer/3');
        self::assertResponseRedirects('/fr_FR/guides/buyer/1');
        $page = $client->followRedirect();

        self::assertCount(1, $page->filter('[data-journey-reset-notice]'));
        self::assertStringContainsString('remise à zéro', $page->filter('[data-journey-reset-notice]')->text());
        self::assertSame('Étape 1 sur 4', trim($page->filter('[data-journey-counter]')->text()));
        $client->submit($page->filter('[data-journey-next]')->form(), [], ['HTTP_ORIGIN' => 'http://localhost']);
        $again = $client->request('GET', '/fr_FR/guides/buyer/1');
        self::assertCount(0, $again->filter('[data-journey-reset-notice]'), 'The message goes away once the visitor acts.');
    }

    public function testTheListOffersToResumeAtTheLastStepSeen(): void
    {
        $client = self::createClient();
        $this->login($client, self::CLAIRE);
        $client->submit($client->request('GET', '/fr_FR/guides/buyer/2')->filter('[data-journey-next]')->form(), [], ['HTTP_ORIGIN' => 'http://localhost']);

        $list = $client->request('GET', '/fr_FR/guides');

        self::assertSame('/fr_FR/guides/buyer/3', $list->filter('li[data-journey="buyer"] a[data-journey-resume]')->attr('href'));
    }

    // ---- US-117: the campaign tags the launch of a journey and the contact clicks, nothing else ----

    public function testTheCampaignOfTheLinkTagsTheLaunchAndTheContactClick(): void
    {
        $this->setEnvironment(['DEMO_CONTACT_URL' => self::CONTACT]);
        $client = self::createClient();
        $this->login($client, self::CLAIRE);
        $client->request('GET', '/fr_FR/guides?utm_source=linkedin&utm_campaign=Ouverture-Demo');

        $step = $client->request('GET', '/fr_FR/guides/buyer/3');
        self::assertSame('ouverture-demo', $step->filter('[data-journey-launch]')->attr('data-umami-event-campaign'));
        self::assertSame('demo-journey-launch', $step->filter('[data-journey-launch]')->attr('data-umami-event'));

        $exit = $client->request('GET', '/fr_FR/guides/buyer/5');
        self::assertSame('ouverture-demo', $exit->filter('[data-journey-contact]')->attr('data-umami-event-campaign'));
        self::assertNull($step->filter('[data-journey-open]')->attr('data-umami-event-campaign'), 'Only the launch and the contact are tagged.');
    }

    public function testTheContactOfAnyPageIsTaggedWithTheCampaignOfTheUrl(): void
    {
        $this->setEnvironment(['DEMO_CONTACT_URL' => self::CONTACT, 'DEMO_PROFILE_URL' => '']);

        $page = self::createClient()->request('GET', '/fr_FR/a-propos?utm_campaign=ouverture-demo');

        self::assertSame('ouverture-demo', $page->filter('[data-cta="about"] a[data-cta-contact]')->attr('data-umami-event-campaign'));
    }

    /** @return iterable<string, array{string}> */
    public static function unexpectedCampaigns(): iterable
    {
        yield 'too long' => [str_repeat('a', 41)];
        yield 'with markup' => ['"><script>alert(1)</script>'];
        yield 'an e-mail address' => ['claire@acme.example'];
        yield 'an array' => ['x[]=1'];
    }

    #[DataProvider('unexpectedCampaigns')]
    public function testAnUnexpectedCampaignIsIgnoredAndNothingIsStored(string $campaign): void
    {
        $this->setEnvironment(['DEMO_CONTACT_URL' => self::CONTACT]);
        $client = self::createClient();
        $this->login($client, self::CLAIRE);
        $client->request('GET', '/fr_FR/guides?utm_campaign=' . rawurlencode($campaign));

        $exit = $client->request('GET', '/fr_FR/guides/buyer/5');
        $step = $client->request('GET', '/fr_FR/guides/buyer/3');

        self::assertCount(1, $exit->filter('[data-journey-contact]'));
        self::assertNull($exit->filter('[data-journey-contact]')->attr('data-umami-event-campaign'));
        self::assertNull($step->filter('[data-journey-launch]')->attr('data-umami-event-campaign'));
        self::assertNull($client->getRequest()->getSession()->get('demo_campaign'));
    }

    public function testNoCampaignIsRememberedOutsideTheJourneyPages(): void
    {
        $this->setEnvironment(['DEMO_CONTACT_URL' => self::CONTACT]);
        $client = self::createClient();
        $client->request('GET', '/fr_FR/plugins?utm_campaign=ouverture-demo');

        $step = $client->request('GET', '/fr_FR/guides/buyer/5');

        self::assertNull($step->filter('[data-journey-contact]')->attr('data-umami-event-campaign'));
    }

    public function testWithoutConfigurationNoRequestToUmamiIsEmitted(): void
    {
        $this->setEnvironment(['DEMO_UMAMI_SCRIPT_URL' => '', 'DEMO_UMAMI_WEBSITE_ID' => '']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/guides/buyer/1?utm_campaign=ouverture-demo');

        self::assertStringNotContainsString('umami.is', (string) $client->getResponse()->getContent());
        self::assertCount(0, $client->getCrawler()->filter('script[src]:not([src^="/"])'));
    }

    // ---- US-106: every step of every journey leads somewhere, with the account of the journey ----

    /** @return iterable<string, array{string, int, string}> */
    public static function everyStep(): iterable
    {
        foreach (['buyer' => 4, 'approver' => 4, 'finance' => 4, 'ecommerce' => 4, 'it' => 4] as $journey => $count) {
            for ($step = 1; $step <= $count; ++$step) {
                yield $journey . ' ' . $step => [$journey, $step, 'fr_FR'];
            }
        }
    }

    #[DataProvider('everyStep')]
    public function testEveryStepOpensItsScreenWithTheAccountOfTheJourney(string $journeyId, int $number, string $locale): void
    {
        self::bootKernel();
        $catalog = self::getContainer()->get('test.demo_journey_catalog');
        $launcher = self::getContainer()->get('test.demo_journey_launcher');
        self::assertInstanceOf(JourneyCatalog::class, $catalog);
        self::assertInstanceOf(JourneyLauncher::class, $launcher);
        $journey = $catalog->find($journeyId);
        self::assertNotNull($journey, $journeyId);
        $step = $journey->step($number);
        self::assertNotNull($step, $journeyId . ' step ' . $number);
        self::ensureKernelShutdown();
        $where = \sprintf('journey "%s" step %d (route %s)', $journeyId, $number, $step->route);
        self::assertTrue(null === $step->scenario || $launcher->knows($step->scenario), $where . ': unknown scenario');

        foreach (['fr_FR', 'en_US'] as $locale) {
            self::ensureKernelShutdown();
            $client = self::createClient();
            $client->catchExceptions(false);
            $this->login($client, $journey->accountEmail);
            $page = $client->request('GET', '/' . $locale . '/guides/' . $journeyId . '/' . $number);
            self::assertResponseIsSuccessful($where . ' ' . $locale);
            self::assertStringNotContainsString('journey.', $page->filter('[data-journey-body]')->text(), $where . ': raw translation key');
            $target = (string) $page->filter('[data-journey-open]')->attr('href');

            $client->request('GET', $target);
            self::assertLessThan(400, $client->getResponse()->getStatusCode(), $where . ' -> ' . $target);
        }
    }

    /** The number of lines of the current cart of Claire. */
    private function cartLines(): int
    {
        return $this->intValue(
            "SELECT COUNT(i.id) FROM sylius_order_item i JOIN sylius_order o ON o.id = i.order_id JOIN sylius_customer c ON c.id = o.customer_id WHERE o.state = 'cart' AND c.email = ?",
            [self::CLAIRE],
        );
    }

    private function cartQuantity(): int
    {
        return $this->intValue(
            "SELECT COALESCE(SUM(i.quantity), 0) FROM sylius_order_item i JOIN sylius_order o ON o.id = i.order_id JOIN sylius_customer c ON c.id = o.customer_id WHERE o.state = 'cart' AND c.email = ?",
            [self::CLAIRE],
        );
    }

    /** The cart of a shared demo account is changed by a launch: everything runs in a transaction that tearDown rolls back. */
    private function beginTransaction(): void
    {
        $this->transaction = $this->db();
        $this->transaction->beginTransaction();
    }

    private function login(KernelBrowser $client, string $email): void
    {
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
