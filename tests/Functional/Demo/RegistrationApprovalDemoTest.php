<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\DataFixtures\PendingRegistrationDemoFixture;
use Doctrine\DBAL\Connection as Dbal;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mime\Email;

/**
 * US-020 — A registration on the B2B channel waits for Sophie (restricted back-office account); she approves or
 * rejects it from the admin, the customer is told by e-mail, an approved customer sees the prices. Everything
 * happens in a transaction that is rolled back. Run against a database loaded with the `demo` fixture suite.
 */
final class RegistrationApprovalDemoTest extends DemoAdminWebTestCase
{
    use McpAccess;

    private const PRICE = '/\d[,.]\d\d(?:\s|&nbsp;|\x{00A0}|\x{202F})*(?:€|&euro;|EUR)/u';

    private const PASSWORD = 'Demo1234!x';

    private ?Dbal $transactional = null;

    protected function tearDown(): void
    {
        if ($this->transactional instanceof Dbal && $this->transactional->isTransactionActive()) {
            $this->transactional->rollBack();
        }
        parent::tearDown();
    }

    public function testTheExistingDemoAccountsAreApprovedAndOnlyTheFictitiousRegistrationsWait(): void
    {
        self::createClient();

        // demo_pending_registrations (US-243): the two registrations Sophie decides on in the public demo.
        $waiting = $this->db()->fetchFirstColumn("SELECT email_canonical FROM sylius_customer WHERE approval_status <> 'approved' ORDER BY email_canonical");
        self::assertSame(array_column(PendingRegistrationDemoFixture::REGISTRATIONS, 0), $waiting);
    }

    public function testARegistrationWaitsThenSophieApprovesItAndTheCustomerSeesThePrices(): void
    {
        $client = $this->clientInTransaction();
        $email = $this->register($client);

        // Pending: signed out after the registration, the received page, no access to the prices.
        self::assertSame('pending', $this->statusOf($email));
        $client->request('GET', '/fr_FR/account/dashboard');
        self::assertResponseRedirects();
        $this->signInAs($client, $email);
        $slug = $this->firstProductSlug();
        $html = (string) $client->request('GET', '/fr_FR/products/' . $slug)->html();
        self::assertResponseIsSuccessful();
        self::assertDoesNotMatchRegularExpression(self::PRICE, $html);
        self::assertStringContainsString('Connectez-vous pour voir vos prix', $html);
        $client->request('GET', '/fr_FR/cart/');
        self::assertResponseStatusCodeSame(403);
        $page = trim((string) preg_replace('/\\s+/', ' ', strip_tags((string) $client->getResponse()->getContent())));
        self::assertStringContainsString('en attente', $page, substr($page, 0, 700));

        // Sophie sees it in the list and approves it.
        $mails = $this->decide($client, $email, 'approve');

        self::assertSame('approved', $this->statusOf($email));
        self::assertCount(1, $mails);
        self::assertInstanceOf(Email::class, $mails[0]);
        self::assertSame($email, $mails[0]->getTo()[0]->getAddress());
        self::assertStringContainsString('approuvé', (string) $mails[0]->getSubject());

        // At the next request the customer sees the prices.
        $this->signInAs($client, $email);
        $html = (string) $client->request('GET', '/fr_FR/products/' . $slug)->html();
        self::assertMatchesRegularExpression(self::PRICE, $html);
        self::assertStringNotContainsString('data-test-tmonier-prices-hidden', $html);
    }

    public function testSophieRejectsARegistrationAndTheCustomerStaysOut(): void
    {
        $client = $this->clientInTransaction();
        $email = $this->register($client);

        $mails = $this->decide($client, $email, 'reject', 'Hors périmètre');

        self::assertSame('rejected', $this->statusOf($email));
        self::assertCount(1, $mails);
        self::assertInstanceOf(Email::class, $mails[0]);
        self::assertStringNotContainsString('Hors périmètre', (string) $mails[0]->getHtmlBody());
        $this->signInAs($client, $email);
        $client->request('GET', '/fr_FR/cart/');
        self::assertResponseStatusCodeSame(403);
        $crawler = $client->request('GET', '/admin/registration-approvals');
        self::assertStringNotContainsString($email, $crawler->filter('body')->text());
    }

    public function testTheEmailIsInTheLanguageOfTheChannel(): void
    {
        $client = $this->clientInTransaction();
        $email = $this->register($client);
        $this->db()->executeStatement("UPDATE sylius_channel SET default_locale_id = (SELECT id FROM sylius_locale WHERE code = 'en_US') WHERE code = 'B2B'");

        $mails = $this->decide($client, $email, 'approve');

        self::assertInstanceOf(Email::class, $mails[0]);
        self::assertStringContainsString('approved', (string) $mails[0]->getSubject());
    }

    public function testAnotherRegistrationWithTheSameAddressRevealsNothingAndChangesNothing(): void
    {
        $client = $this->clientInTransaction();
        $email = $this->register($client);
        $customers = $this->intValue('SELECT COUNT(*) FROM sylius_customer');

        $crawler = $client->request('GET', '/fr_FR/register');
        $form = $crawler->filter('form[name="sylius_shop_customer_registration"]')->form();
        $form['sylius_shop_customer_registration[firstName]'] = 'Autre';
        $form['sylius_shop_customer_registration[lastName]'] = 'Personne';
        $form['sylius_shop_customer_registration[email]'] = $email;
        $form['sylius_shop_customer_registration[user][plainPassword][first]'] = self::PASSWORD;
        $form['sylius_shop_customer_registration[user][plainPassword][second]'] = self::PASSWORD;
        $client->submit($form);
        $client->followRedirect();

        self::assertSame($customers, $this->intValue('SELECT COUNT(*) FROM sylius_customer'));
        self::assertStringContainsString('data-test-tmonier-registration-received', (string) $client->getResponse()->getContent());
        // Same address as the demo accounts: also hidden.
        self::assertStringNotContainsStringIgnoringCase('already used', (string) $client->getResponse()->getContent());
    }

    public function testAPendingCustomerCannotGiveAnAiAssistantTheirAccessNorUseOne(): void
    {
        $client = $this->clientInTransaction();
        $pending = $this->register($client);
        $token = $this->mcpAccessToken($pending);

        $this->signInAs($client, $pending);
        $client->request('GET', '/fr_FR/account/ai-assistants/authorize');
        self::assertResponseStatusCodeSame(403, 'the consent screen');
        $client->request('GET', '/fr_FR/account/ai-assistants');
        self::assertResponseStatusCodeSame(403, 'the connections');
        $this->mcpCall($client, $token);
        self::assertResponseStatusCodeSame(403, 'an access token the customer got before: the assistant has the access of the customer');
        self::assertStringNotContainsString('result', (string) $client->getResponse()->getContent());

        // Once approved, the same token works.
        $this->decide($client, $pending, 'approve');
        $this->mcpCall($client, $token);
        self::assertNotContains($client->getResponse()->getStatusCode(), [401, 403]);
    }

    public function testAnApprovedDemoCustomerKeepsHisAiAssistants(): void
    {
        $client = $this->clientInTransaction();
        $token = $this->mcpAccessToken('claire@acme.example');

        $this->mcpCall($client, $token);

        self::assertNotContains($client->getResponse()->getStatusCode(), [401, 403]);
    }

    public function testSophieCannotDoAnythingElseWithThePlugin(): void
    {
        $client = $this->clientInTransaction();
        $email = $this->register($client);
        $id = $this->intValue('SELECT id FROM sylius_customer WHERE email = ?', [$email]);

        // A decision without a valid token is refused, the status is unchanged.
        $client->request('POST', \sprintf('/admin/registration-approvals/%d/approve', $id), ['_csrf_token' => 'forged']);

        self::assertResponseStatusCodeSame(403);
        self::assertSame('pending', $this->statusOf($email));
    }

    private function clientInTransaction(): KernelBrowser
    {
        $client = self::createClient();
        $client->disableReboot();
        $client->enableProfiler();
        $this->transactional = $this->db();
        $this->transactional->beginTransaction();
        $client->loginUser($this->sophie(), 'admin');

        return $client;
    }

    /** Registers through the shop form of the B2B channel and returns the address. */
    private function register(KernelBrowser $client): string
    {
        $email = 'visiteur-' . bin2hex(random_bytes(4)) . '@client.example';
        $crawler = $client->request('GET', '/fr_FR/register');
        $form = $crawler->filter('form[name="sylius_shop_customer_registration"]')->form();
        $form['sylius_shop_customer_registration[firstName]'] = 'Alice';
        $form['sylius_shop_customer_registration[lastName]'] = 'Visiteuse';
        $form['sylius_shop_customer_registration[email]'] = $email;
        $form['sylius_shop_customer_registration[user][plainPassword][first]'] = self::PASSWORD;
        $form['sylius_shop_customer_registration[user][plainPassword][second]'] = self::PASSWORD;
        $client->submit($form);
        $client->followRedirect();
        self::assertStringContainsString('data-test-tmonier-registration-received', (string) $client->getResponse()->getContent());

        return $email;
    }

    /**
     * Sophie opens the list, finds the row and posts the decision of its form.
     *
     * @return array<\Symfony\Component\Mime\RawMessage>
     */
    private function decide(KernelBrowser $client, string $email, string $decision, ?string $reason = null): array
    {
        $crawler = $client->request('GET', '/admin/registration-approvals');
        self::assertResponseIsSuccessful();
        $row = $crawler->filter('tr')->reduce(static fn (Crawler $tr): bool => str_contains($tr->text(), $email));
        self::assertCount(1, $row, 'the pending registration is listed');
        $token = (string) $row->filter(\sprintf('[data-test-tmonier-%s] input[name="_csrf_token"]', $decision))->attr('value');

        $client->request('POST', \sprintf('/admin/registration-approvals/%d/%s', $this->intValue('SELECT id FROM sylius_customer WHERE email = ?', [$email]), $decision), ['_csrf_token' => $token, 'reason' => $reason]);
        $mails = $this->getMailerMessages();
        self::assertResponseRedirects('/admin/registration-approvals');

        return $mails;
    }

    private function signInAs(KernelBrowser $client, string $email): void
    {
        $id = $this->intValue('SELECT u.id FROM sylius_shop_user u JOIN sylius_customer c ON c.id = u.customer_id WHERE c.email = ?', [$email]);
        $repository = self::getContainer()->get('sylius.repository.shop_user');
        self::assertInstanceOf(RepositoryInterface::class, $repository);
        $user = $repository->find($id);
        self::assertInstanceOf(ShopUserInterface::class, $user);
        $client->loginUser($user, 'shop');
    }

    private function statusOf(string $email): string
    {
        $status = $this->db()->fetchOne('SELECT approval_status FROM sylius_customer WHERE email = ?', [$email]);
        self::assertIsString($status);

        return $status;
    }

    private function firstProductSlug(): string
    {
        $slug = $this->db()->fetchOne("SELECT t.slug FROM sylius_product p JOIN sylius_product_translation t ON t.translatable_id = p.id AND t.locale = 'fr_FR' WHERE p.enabled = 1 ORDER BY p.id LIMIT 1");
        self::assertIsString($slug);

        return $slug;
    }
}
