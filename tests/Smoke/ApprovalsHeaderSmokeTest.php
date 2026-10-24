<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-156: the "Approvals" entry of the header shows how many orders wait for the member. In the demo, the company
 * administrator decides too (`approver_roles: [approver, admin]`); a buyer never sees the entry.
 */
final class ApprovalsHeaderSmokeTest extends WebTestCase
{
    private const PAGE = '/fr_FR/account/dashboard';

    public function testPaulTheApproverSeesTheOrderOfClaireWaitingForHim(): void
    {
        $client = self::createClient();
        $this->login($client, 'paul@acme.example');

        $crawler = $client->request('GET', self::PAGE);

        // US-192: on a wide screen the entry is an item of the greeting menu (the layout of the phone menu), not a link of the row.
        self::assertCount(0, $crawler->filter('[data-test-tmonier-header-approvals="desktop"]'));
        $entry = $crawler->filter('[data-b2b-account-menu] [data-test-tmonier-header-approvals="mobile"]');
        self::assertCount(1, $entry);
        self::assertSame('1', trim($entry->filter('[data-test-tmonier-header-approvals-badge]')->text()));
        self::assertStringContainsString('1 approbation en attente', $entry->text());
        self::assertCount(2, $crawler->filter('[data-test-tmonier-header-approvals="mobile"]'), 'the phone menu and the greeting menu');
    }

    public function testIsabelleTheAdministratorHasTheEntryToo(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $crawler = $client->request('GET', self::PAGE);

        self::assertCount(1, $crawler->filter('[data-b2b-account-menu] [data-test-tmonier-header-approvals]'), 'admin is an approver role of the demo.');
    }

    public function testClaireTheBuyerHasNoEntry(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', self::PAGE);

        self::assertCount(0, $crawler->filter('[data-test-tmonier-header-approvals]'));
    }

    public function testAVisitorHasNoEntry(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/fr_FR/');

        self::assertCount(0, $crawler->filter('[data-test-tmonier-header-approvals]'));
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
