<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-024: shared purchase lists. Run against a database loaded with the `demo` fixture suite
 * (list "Atelier" of Acme, created by Claire, 5 items). Acme: Claire (buyer, creator), Paul (approver),
 * Isabelle (admin); TechParts: Marc.
 */
final class CompanyWishlistSmokeTest extends WebTestCase
{
    private const INDEX = '/fr_FR/account/company-wishlists';

    public function testAVisitorIsSentToTheLogin(): void
    {
        $client = self::createClient();
        $client->request('GET', self::INDEX);

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAMemberSeesTheSharedListWithItemsAndQuantities(): void
    {
        $client = self::createClient();
        $this->login($client, 'paul@acme.example');

        $crawler = $client->request('GET', self::INDEX);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Atelier', (string) $client->getResponse()->getContent());

        $crawler = $client->click($crawler->filter('[data-test-company-wishlist-link]')->link());
        self::assertResponseIsSuccessful();
        self::assertCount(5, $crawler->filter('[data-test-company-wishlist-item]'));
        $quantities = $crawler->filter('[data-test-quantity]')->each(static fn ($node): string => trim($node->text()));
        sort($quantities, \SORT_NUMERIC);
        self::assertSame(['2', '5', '6', '10', '24'], $quantities, 'The quantities of the list are shown.');
        self::assertCount(1, $crawler->filter('[data-test-read-only]'), 'Paul (approver) only reads the list.');
    }

    public function testAMemberAddsTheSelectionToTheCart(): void
    {
        $client = self::createClient();
        $this->login($client, 'paul@acme.example');
        $show = $this->showUrl($client);

        $crawler = $client->request('GET', $show);
        $names = $crawler->filter('[data-test-company-wishlist-item] td:nth-child(2)')->each(static fn ($node): string => trim($node->text()));
        self::assertCount(5, $names);

        $form = $crawler->filter('#company-wishlist-cart');
        $ids = $form->filter('input[name="items[]"]')->each(static fn ($node): string => (string) $node->attr('value'));
        $client->request('POST', (string) $form->attr('action'), [
            '_csrf_token' => (string) $form->filter('input[name="_csrf_token"]')->attr('value'),
            'items' => [$ids[0], $ids[1]],
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($names[0], $content);
        self::assertStringContainsString($names[1], $content);
        foreach (\array_slice($names, 2) as $other) {
            self::assertStringNotContainsString($other, $content, 'Only the 2 selected items are in the cart.');
        }
    }

    public function testTheCreatorAndTheAdminEditButAMemberCannot(): void
    {
        $client = self::createClient();
        $this->login($client, 'paul@acme.example');
        $show = $this->showUrl($client);

        $client->request('POST', $show . '/items/1/remove', ['_csrf_token' => 'whatever']);
        self::assertResponseStatusCodeSame(403);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');
        $crawler = $client->request('GET', $show);
        self::assertCount(5, $crawler->filter('button[form^="remove-"]'), 'The admin sees the remove buttons.');
    }

    public function testAMemberOfAnotherCompanyIsRefused(): void
    {
        $client = self::createClient();
        $this->login($client, 'paul@acme.example');
        $show = $this->showUrl($client);

        self::ensureKernelShutdown();
        $client = self::createClient();
        $this->login($client, 'marc@techparts.example');
        $client->request('GET', $show);

        self::assertResponseStatusCodeSame(403);
    }

    private function showUrl(KernelBrowser $client): string
    {
        $crawler = $client->request('GET', self::INDEX);

        return (string) $crawler->filter('[data-test-company-wishlist-link]')->attr('href');
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
