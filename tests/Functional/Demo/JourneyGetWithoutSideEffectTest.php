<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Command\ResetStatus;
use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-160: a GET on a guided journey changes nothing in the session (prefetch, repeated load, crawler). The progress moves
 * only by the explicit "next" action, a POST protected by a CSRF token.
 */
final class JourneyGetWithoutSideEffectTest extends WebTestCase
{
    use IsolatesSharedAccounts;

    private const CLAIRE = 'claire@acme.example';

    private const ORIGIN = ['HTTP_ORIGIN' => 'http://localhost'];

    public function testLoadingAStepManyTimesOrPrefetchedLeavesTheSessionUntouched(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, self::CLAIRE);

        foreach (['/fr_FR/guides/buyer/1', '/fr_FR/guides/buyer/2', '/fr_FR/guides/buyer/2', '/fr_FR/guides'] as $url) {
            $client->request('GET', $url);
            self::assertResponseIsSuccessful($url);
            self::assertSame([], $this->journeyKeys($client), $url . ' must not write in the session.');
        }
        $client->request('GET', '/fr_FR/guides/buyer/3', server: ['HTTP_SEC_PURPOSE' => 'prefetch', 'HTTP_PURPOSE' => 'prefetch']);
        self::assertSame([], $this->journeyKeys($client), 'A prefetch must not move the progress.');
    }

    public function testAnAnonymousVisitorGetsNoSessionDataEither(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);

        $client->request('GET', '/en_US/guides/buyer/1');

        self::assertResponseIsSuccessful();
        self::assertSame([], $this->journeyKeys($client));
    }

    public function testNextIsAPostActionThatRecordsTheProgressAndRedirectsToTheNextStep(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, self::CLAIRE);
        $page = $client->request('GET', '/fr_FR/guides/buyer/1');

        $next = $page->filter('form[data-journey-next-form]');
        self::assertCount(1, $next);
        self::assertSame('post', strtolower((string) $next->attr('method')));
        $client->submit($next->form(), [], self::ORIGIN);

        self::assertResponseRedirects('/fr_FR/guides/buyer/2', 303);
        $list = $client->request('GET', '/fr_FR/guides');
        self::assertSame('/fr_FR/guides/buyer/2', $list->filter('li[data-journey="buyer"] a[data-journey-resume]')->attr('href'));
    }

    public function testNextWithoutAValidTokenIsRefusedAndMovesNothing(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, self::CLAIRE);

        $client->request('POST', '/fr_FR/guides/buyer/1/advance', ['_token' => 'nope']);

        self::assertResponseStatusCodeSame(403);
        $list = $client->request('GET', '/fr_FR/guides');
        self::assertCount(0, $list->filter('li[data-journey="buyer"] a[data-journey-resume]'));
    }

    public function testNextIsNotReachableByGet(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);

        $client->request('GET', '/fr_FR/guides/buyer/1/advance');

        self::assertResponseStatusCodeSame(405);
    }

    public function testThereIsNoNextAfterTheExitScreen(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, self::CLAIRE);

        $client->request('POST', '/fr_FR/guides/buyer/5/advance', ['_token' => 'csrf-token'], server: self::ORIGIN);
        self::assertResponseStatusCodeSame(404);

        $client->request('POST', '/fr_FR/guides/buyer/99/advance', ['_token' => 'csrf-token'], server: self::ORIGIN);
        self::assertResponseStatusCodeSame(404);
    }

    public function testTheResetNoticeStaysUntilTheVisitorActsAndReadingItChangesNothing(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, self::CLAIRE);
        $page = $client->request('GET', '/fr_FR/guides/buyer/1');
        $client->submit($page->filter('form[data-journey-next-form]')->form(), [], self::ORIGIN);
        $markerFile = self::getContainer()->getParameter('demo.reset_marker_file');
        self::assertIsString($markerFile);
        new ResetStatus($markerFile)->markSucceeded();

        $client->request('GET', '/fr_FR/guides/buyer/2');
        self::assertResponseRedirects('/fr_FR/guides/buyer/1');
        $first = $client->followRedirect();
        $second = $client->request('GET', '/fr_FR/guides/buyer/1');

        self::assertCount(1, $first->filter('[data-journey-reset-notice]'));
        self::assertCount(1, $second->filter('[data-journey-reset-notice]'), 'A GET does not consume the notice.');
        $list = $client->request('GET', '/fr_FR/guides');
        self::assertCount(0, $list->filter('li[data-journey="buyer"] a[data-journey-resume]'), 'The old progress is gone after the reset.');

        $client->submit($second->filter('form[data-journey-next-form]')->form(), [], self::ORIGIN);
        $after = $client->request('GET', '/fr_FR/guides/buyer/2');
        self::assertCount(0, $after->filter('[data-journey-reset-notice]'), 'Acting acknowledges the reset.');
    }

    /** @return list<string> the session keys written by the journeys or the CSRF layer */
    private function journeyKeys(KernelBrowser $client): array
    {
        $session = $client->getRequest()->getSession();

        return array_values(array_filter(
            array_map('strval', array_keys($session->all())),
            static fn (string $key): bool => str_starts_with($key, 'demo_journeys') || str_contains($key, 'csrf'),
        ));
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
