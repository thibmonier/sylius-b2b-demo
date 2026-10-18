<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

/**
 * US-097: the back-office screens the audit found empty show the living data of the fixtures to Sophie (T-097-08,
 * French and English). The data itself is checked by tests/Smoke/LiveDemoDataSmokeTest.
 */
final class LiveAdminScreensTest extends DemoAdminWebTestCase
{
    public function testTheAuditLogOpensAndShowsTheBusinessEventsOfTheTeam(): void
    {
        $client = $this->sophieClient();
        // Other tests write entries too and the list is paginated: the fixture's entry is opened by its id.
        $id = $this->db()->fetchOne('SELECT id FROM tmonier_audit_log_entry WHERE route = ? AND actor_name = ? ORDER BY id LIMIT 1', ['sylius_admin_order_update', 'Sophie Martin']);
        self::assertNotFalse($id, 'The fixture wrote its entries.');

        $client->request('GET', '/admin/audit-log');
        self::assertResponseIsSuccessful();
        $client->request('GET', '/admin/audit-log/' . (is_scalar($id) ? (string) $id : ''));

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('validation demandée à Paul Martin', (string) $client->getResponse()->getContent());
    }

    public function testTheCopilotListsDemonstrationSuggestionsInFrenchAndEnglish(): void
    {
        foreach (['fr_FR' => 'Générateur de démonstration', 'en_US' => 'Demo generator'] as $locale => $notice) {
            self::ensureKernelShutdown();
            $client = $this->sophieClient($locale);

            $client->request('GET', '/admin/ai-suggestions/');

            self::assertResponseIsSuccessful();
            self::assertGreaterThan(0, $client->getCrawler()->filter('table tbody tr')->count(), 'Suggestions are listed (' . $locale . ').');
            self::assertStringContainsString($notice, (string) $client->getResponse()->getContent());
        }
    }

    public function testTheOrdersInPreparationAreListed(): void
    {
        $client = $this->sophieClient();

        $client->request('GET', '/admin/order-drafts');

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('henri@globalmaint.example', $content);
        self::assertStringContainsString('marc@techparts.example', $content);
    }

    public function testTheOpenItemsListTheOverdueOrderOfAcme(): void
    {
        $client = $this->sophieClient();
        $number = $this->db()->fetchOne('SELECT number FROM sylius_order WHERE po_reference = ?', ['ACME-PO-2026-0568']);
        self::assertIsString($number);

        $client->request('GET', '/admin/trade-credit/open-items');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($number, (string) $client->getResponse()->getContent());
    }

    public function testTheQuoteRequestsAreNumberedWithTheCurrentYear(): void
    {
        $client = $this->sophieClient();

        $client->request('GET', '/admin/quote-requests/');

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        foreach (['001', '002', '003'] as $sequence) {
            self::assertStringContainsString(sprintf('Q-%s-%s', date('Y'), $sequence), $content);
        }
    }
}
