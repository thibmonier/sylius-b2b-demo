<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/** US-198: the "add to cart" button of a card of the list posts to the demo, which adds the variant to the cart (the quick order adder) and comes back to the list. */
final class ProductListAddToCartTest extends WebTestCase
{
    use DatabaseAccess;
    use IsolatesSharedAccounts;

    public function testTheButtonOfACardAddsOneUnitAndComesBackToTheList(): void
    {
        $client = $this->signedIn('claire@acme.example');
        $page = $client->request('GET', '/fr_FR/taxons/visserie');
        $form = $this->firstInStockForm($page);
        $code = (string) $form->getValues()['code'];
        $before = $this->quantityInCart($code);

        $client->submit($form);

        self::assertResponseRedirects();
        self::assertStringContainsString('/fr_FR/taxons/visserie', (string) $client->getResponse()->headers->get('Location'));
        self::assertSame($before + 1, $this->quantityInCart($code));
    }

    public function testAnInvalidTokenAddsNothing(): void
    {
        $client = $this->signedIn('claire@acme.example');
        $page = $client->request('GET', '/fr_FR/taxons/visserie');
        $form = $this->firstInStockForm($page);
        $code = (string) $form->getValues()['code'];
        $before = $this->quantityInCart($code);
        $form['_csrf_token'] = 'not-the-token';

        $client->submit($form);

        self::assertResponseRedirects();
        self::assertSame($before, $this->quantityInCart($code));
    }

    public function testTheReturnAddressStaysOnTheSite(): void
    {
        $client = $this->signedIn('claire@acme.example');
        $form = $this->firstInStockForm($client->request('GET', '/fr_FR/taxons/visserie'));
        $form['return'] = 'https://elsewhere.example/phish';

        $client->submit($form);

        self::assertResponseRedirects();
        self::assertStringNotContainsString('elsewhere.example', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAVisitorIsSentToTheLoginAndAddsNothing(): void
    {
        $client = self::createClient();
        $client->request('POST', '/fr_FR/cart/add-from-list', ['code' => 'VISS-001', 'quantity' => 1, '_csrf_token' => 'x']);

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    private function firstInStockForm(Crawler $page): \Symfony\Component\DomCrawler\Form
    {
        $button = $page->filter('.products-grid [data-b2b-card] form[action$="/cart/add-from-list"] button[type="submit"]')->first();
        self::assertCount(1, $button, 'an in-stock card with its button');

        return $button->form();
    }

    private function quantityInCart(string $code): int
    {
        return $this->intValue("SELECT COALESCE(SUM(i.quantity), 0) FROM sylius_order_item i JOIN sylius_order o ON o.id = i.order_id JOIN sylius_product_variant v ON v.id = i.variant_id JOIN sylius_customer c ON c.id = o.customer_id WHERE o.state = 'cart' AND c.email = 'claire@acme.example' AND v.code = ?", [$code]);
    }

    private function signedIn(string $email): KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
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
