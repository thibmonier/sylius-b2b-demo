<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-025: shared address book of Acme (company-accounts 1.2). Run against a database loaded with the `demo`
 * fixture suite: 3 Acme addresses ("Usine de Lyon"…), managed by Isabelle (admin); Claire (buyer) reads them
 * and picks one at the checkout. The cart is filled from the shared list "Atelier" (US-024).
 */
final class CompanyAddressBookSmokeTest extends WebTestCase
{
    private const BOOK = '/fr_FR/account/company/addresses';

    private const CHECKOUT_ADDRESS = '/fr_FR/checkout/address';

    private const LABELS = ['Usine de Lyon', 'Entrepôt de Saint-Priest', 'Atelier de Grenoble'];

    public function testClaireDeliversToTheLyonPlantFromTheCheckout(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');
        $this->fillCart($client);

        $crawler = $client->request('GET', self::CHECKOUT_ADDRESS);
        self::assertResponseIsSuccessful();
        $picker = $crawler->filter('[data-test-tmonier-checkout-company-addresses]');
        self::assertCount(1, $picker, 'The checkout shows the shared addresses of Acme.');
        foreach (self::LABELS as $label) {
            self::assertStringContainsString($label, $picker->text());
        }

        $lyon = $picker->filter('[data-test-tmonier-checkout-company-address]')->reduce(
            static fn (Crawler $entry): bool => str_contains($entry->text(), 'Usine de Lyon'),
        );
        self::assertCount(1, $lyon);
        $client->submit($lyon->filter('form')->form());

        self::assertResponseRedirects();
        self::assertStringEndsWith(self::CHECKOUT_ADDRESS, (string) $client->getResponse()->headers->get('Location'));
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSame('12 rue de l\'Industrie', $this->field($crawler, 'street'), 'The shipping address is the Lyon plant.');
        self::assertSame('69003', $this->field($crawler, 'postcode'));
        self::assertSame('Lyon', $this->field($crawler, 'city'));
    }

    public function testClaireReadsTheBookButCannotChangeIt(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $crawler = $client->request('GET', self::BOOK);
        self::assertResponseIsSuccessful();
        self::assertCount(3, $crawler->filter('[data-test-tmonier-company-address]'));
        self::assertCount(0, $crawler->filter('[data-test-tmonier-company-address-add]'), 'A buyer has no add button.');
        self::assertCount(0, $crawler->filter('[data-test-tmonier-company-address-delete]'), 'A buyer has no delete button.');

        $id = (string) $crawler->filter('[data-test-tmonier-company-address]')->first()->attr('data-test-tmonier-company-address');
        $client->request('GET', self::BOOK . '/new');
        self::assertResponseStatusCodeSame(403);
        $client->request('POST', self::BOOK . '/' . $id . '/delete', ['_csrf_token' => 'whatever']);
        self::assertResponseStatusCodeSame(403);
    }

    public function testIsabelleManagesTheBook(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $crawler = $client->request('GET', self::BOOK);
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('[data-test-tmonier-company-address-add]'));
        self::assertCount(3, $crawler->filter('[data-test-tmonier-company-address-delete]'));

        $client->request('GET', self::BOOK . '/new');
        self::assertResponseIsSuccessful();
    }

    public function testAMemberOfAnotherCompanyDoesNotSeeTheAcmeAddresses(): void
    {
        $client = self::createClient();
        $this->login($client, 'marc@techparts.example');

        $crawler = $client->request('GET', self::BOOK);
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('[data-test-tmonier-company-address]'));
        foreach (self::LABELS as $label) {
            self::assertStringNotContainsString($label, (string) $client->getResponse()->getContent());
        }
    }

    /** Adds the first item of the shared list "Atelier" to the cart of the signed-in member. */
    private function fillCart(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', '/fr_FR/account/company-wishlists');
        $crawler = $client->request('GET', (string) $crawler->filter('[data-test-company-wishlist-link]')->attr('href'));
        $form = $crawler->filter('#company-wishlist-cart');
        $client->request('POST', (string) $form->attr('action'), [
            '_csrf_token' => (string) $form->filter('input[name="_csrf_token"]')->attr('value'),
            'items' => [(string) $form->filter('input[name="items[]"]')->first()->attr('value')],
        ]);
        self::assertResponseRedirects();
    }

    private function field(Crawler $crawler, string $name): string
    {
        $input = $crawler->filter(sprintf('input[name$="[shippingAddress][%s]"]', $name));
        self::assertCount(1, $input, sprintf('The shipping %s field is on the address step.', $name));

        return (string) $input->attr('value');
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
