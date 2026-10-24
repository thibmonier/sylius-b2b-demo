<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-184: the transmissions of the e-invoices in the back office (Integrations menu) and the relaunch of a send in error.
 * Run against a database loaded with the `demo` fixtures: the order TP-2026-142 meets the outage of the fictitious platform
 * (status "error"); the writes of a test are rolled back.
 */
final class EinvoiceTransmissionsAdminTest extends DemoAdminWebTestCase
{
    private const ORDER_IN_ERROR = 'TP-2026-142';

    private const ORDER_DEPOSITED = 'ACME-PO-2026-0412';

    private const URL = '/admin/demo/einvoice';

    protected function tearDown(): void
    {
        if ($this->db()->isTransactionActive()) {
            $this->db()->rollBack();
        }
        parent::tearDown();
    }

    /** @return iterable<string, array{string, string, string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'simulés', 'Relancer', 'Rejetée'];
        yield 'English' => ['en_US', 'simulated', 'Relaunch', 'Rejected'];
    }

    #[DataProvider('locales')]
    public function testTheListShowsEveryInvoiceWithItsStatusReasonAndDateAndTheSimulatedBanner(string $locale, string $simulated, string $button, string $rejected): void
    {
        $client = $this->sophieClient($locale);

        $page = $client->request('GET', self::URL);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($simulated, mb_strtolower($page->filter('[data-einvoice-banner]')->text()));
        self::assertCount($this->intValue('SELECT COUNT(*) FROM tmonier_einvoice_transmission'), $page->filter('[data-einvoice-row]'));
        $row = $page->filter('[data-einvoice-row="' . $this->invoiceNumberOf(self::ORDER_DEPOSITED) . '"]');
        self::assertSame('deposited', $row->filter('[data-einvoice-status]')->attr('data-einvoice-status'));
        self::assertNotSame('', trim($row->filter('time')->text()));
        $rejectedRow = $page->filter('[data-einvoice-row="' . $this->invoiceNumberOf('ACME-PO-2026-0458') . '"]');
        self::assertStringContainsString($rejected, $rejectedRow->filter('[data-einvoice-status]')->text());
        self::assertStringContainsString('Simulated reason', $rejectedRow->filter('[data-einvoice-message]')->text());
    }

    #[DataProvider('locales')]
    public function testOnlyTheRowsInErrorOfferTheRelaunchButton(string $locale, string $simulated, string $button): void
    {
        $page = $this->sophieClient($locale)->request('GET', self::URL);

        $withButton = $page->filter('[data-einvoice-row]')->reduce(static fn (Crawler $row): bool => $row->filter('form[data-einvoice-relaunch]')->count() > 0);
        self::assertSame([$this->invoiceNumberOf(self::ORDER_IN_ERROR)], $withButton->each(static fn (Crawler $row): string => (string) $row->attr('data-einvoice-row')));
        self::assertStringContainsString($button, $withButton->filter('form[data-einvoice-relaunch] button')->text());
        self::assertSame('error', $withButton->filter('[data-einvoice-status]')->attr('data-einvoice-status'));
    }

    public function testTheMenuOfTheIntegrationsGroupLinksToTheScreen(): void
    {
        $page = $this->sophieClient()->request('GET', '/admin/');

        self::assertGreaterThan(0, $page->filter('a[href$="' . self::URL . '"]')->count());
    }

    public function testRelaunchingASendInErrorDepositsItOnTheSameLineAndConfirms(): void
    {
        $client = $this->transactionalClient();
        $before = $this->intValue('SELECT COUNT(*) FROM tmonier_einvoice_transmission');

        $client->submit($this->relaunchForm($client));
        $page = $client->followRedirect();

        self::assertStringContainsString('relancé', $this->text($page));
        self::assertSame('deposited', $this->statusOf(self::ORDER_IN_ERROR));
        self::assertSame($before, $this->intValue('SELECT COUNT(*) FROM tmonier_einvoice_transmission'), 'same line, no second one');
        self::assertCount(0, $client->request('GET', self::URL)->filter('form[data-einvoice-relaunch]'), 'no more button once deposited');
    }

    public function testRelaunchingAnInvoiceThatIsNotInErrorIsRefusedWithAClearMessage(): void
    {
        $client = $this->transactionalClient();
        $form = $this->relaunchForm($client);
        $depositedId = $this->invoiceIdOf(self::ORDER_DEPOSITED);
        $before = $this->snapshot('tmonier_einvoice_transmission');

        $client->request('POST', self::URL . '/' . $depositedId . '/relaunch', ['_token' => $form->get('_token')->getValue()]);

        self::assertTrue($client->getResponse()->isRedirection() || 422 === $client->getResponse()->getStatusCode() || 409 === $client->getResponse()->getStatusCode());
        $page = $client->getResponse()->isRedirection() ? $client->followRedirect() : $client->getCrawler();
        self::assertStringContainsString('seul un envoi en erreur', $this->text($page));
        self::assertSame($before, $this->snapshot('tmonier_einvoice_transmission'));
    }

    public function testARelaunchWithoutAValidTokenIsRefusedAndNothingIsSent(): void
    {
        $client = $this->transactionalClient();
        $url = $this->relaunchForm($client)->getUri();

        $client->request('POST', $url, ['_token' => 'not-a-valid-token']);
        self::assertResponseStatusCodeSame(403);
        $client->request('POST', $url);
        self::assertResponseStatusCodeSame(403);

        self::assertSame('error', $this->statusOf(self::ORDER_IN_ERROR));
    }

    public function testAVisitorWhoIsNotAnAdministratorCannotSeeOrRelaunch(): void
    {
        $client = self::createClient();
        $id = $this->invoiceIdOf(self::ORDER_IN_ERROR);

        $client->request('GET', self::URL);
        self::assertTrue($client->getResponse()->isRedirection() || \in_array($client->getResponse()->getStatusCode(), [401, 403], true));
        $client->request('POST', self::URL . '/' . $id . '/relaunch', ['_token' => 'x']);
        self::assertTrue($client->getResponse()->isRedirection() || \in_array($client->getResponse()->getStatusCode(), [401, 403], true));

        self::assertSame('error', $this->statusOf(self::ORDER_IN_ERROR));
    }

    private function transactionalClient(): KernelBrowser
    {
        $client = $this->sophieClient();
        $client->disableReboot();
        $this->db()->beginTransaction();

        return $client;
    }

    private function relaunchForm(KernelBrowser $client): \Symfony\Component\DomCrawler\Form
    {
        $page = $client->request('GET', self::URL);
        $form = $page->filter('[data-einvoice-row="' . $this->invoiceNumberOf(self::ORDER_IN_ERROR) . '"] form[data-einvoice-relaunch]');
        self::assertCount(1, $form);

        return $form->form();
    }

    private function invoiceNumberOf(string $poReference): string
    {
        return $this->stringValue('SELECT i.number FROM sylius_invoicing_plugin_invoice i JOIN sylius_order o ON o.id = i.order_id WHERE o.po_reference = ?', [$poReference]);
    }

    private function invoiceIdOf(string $poReference): string
    {
        return $this->stringValue('SELECT i.id FROM sylius_invoicing_plugin_invoice i JOIN sylius_order o ON o.id = i.order_id WHERE o.po_reference = ?', [$poReference]);
    }

    private function statusOf(string $poReference): string
    {
        return $this->stringValue('SELECT t.status FROM tmonier_einvoice_transmission t JOIN sylius_invoicing_plugin_invoice i ON i.id = t.invoice_id COLLATE utf8mb4_unicode_ci JOIN sylius_order o ON o.id = i.order_id WHERE o.po_reference = ?', [$poReference]);
    }
}
