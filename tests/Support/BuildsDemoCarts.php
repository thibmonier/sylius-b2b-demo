<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Carts of the demo accounts for the tests of the cart page: a signed-in client with one kernel and one transaction
 * (rolled back by {@see IsolatesSharedAccounts}), and a cart of the given lines that the shop shows first.
 */
trait BuildsDemoCarts
{
    use IsolatesSharedAccounts;

    private function clientOf(string $email): KernelBrowser
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $user = $this->customer($email)->getUser();
        self::assertNotNull($user, $email . ' must have an account (demo fixtures).');
        $client->loginUser($user, 'shop');

        return $client;
    }

    /** @param array<string, int> $lines */
    private function cartOf(string $email, array $lines, string $channelCode = 'B2B'): OrderInterface
    {
        $container = self::getContainer();
        $channel = $this->entityManager()->getRepository(ChannelInterface::class)->findOneBy(['code' => $channelCode]);
        self::assertInstanceOf(ChannelInterface::class, $channel);
        $orderFactory = $container->get('sylius.factory.order');
        self::assertInstanceOf(FactoryInterface::class, $orderFactory);

        $order = $orderFactory->createNew();
        self::assertInstanceOf(OrderInterface::class, $order);
        $order->setChannel($channel);
        $order->setCurrencyCode('EUR');
        $order->setLocaleCode('fr_FR');
        $order->setCustomerWithAuthorization($this->customer($email));
        foreach ($lines as $sku => $quantity) {
            $order->addItem($this->orderItem($this->variant($sku), $quantity));
        }
        $processor = $container->get('sylius.order_processing.order_processor');
        self::assertInstanceOf(OrderProcessorInterface::class, $processor);
        $processor->process($order);
        // Sylius shows the latest cart of the customer (createdAt DESC, to the second): this one wins, and is rolled back.
        $order->setCreatedAt(new \DateTime('+1 hour'));
        $this->entityManager()->persist($order);
        $this->entityManager()->flush();

        return $order;
    }

    private function get(KernelBrowser $client, string $url): string
    {
        $client->request('GET', $url);
        self::assertResponseIsSuccessful($url);

        return (string) $client->getResponse()->getContent();
    }

    /** The text of the page with the spaces of the money formats (no-break, narrow no-break) turned into plain spaces. */
    private function plain(string $html): string
    {
        return (string) preg_replace('/\s+/u', ' ', str_replace(["\u{00A0}", "\u{202F}"], ' ', strip_tags(html_entity_decode($html, \ENT_QUOTES))));
    }

    private function orderItem(ProductVariantInterface $variant, int $quantity): OrderItemInterface
    {
        $container = self::getContainer();
        $factory = $container->get('sylius.factory.order_item');
        $modifier = $container->get('sylius.modifier.order_item_quantity');
        self::assertInstanceOf(FactoryInterface::class, $factory);
        self::assertInstanceOf(OrderItemQuantityModifierInterface::class, $modifier);

        $item = $factory->createNew();
        self::assertInstanceOf(OrderItemInterface::class, $item);
        $item->setVariant($variant);
        $modifier->modify($item, $quantity);

        return $item;
    }

    private function variant(string $sku): ProductVariantInterface
    {
        $variant = $this->entityManager()->getRepository(ProductVariantInterface::class)->findOneBy(['code' => $sku]);
        self::assertInstanceOf(ProductVariantInterface::class, $variant, $sku . ' must exist (demo fixtures).');

        return $variant;
    }

    private function customer(string $email): CustomerInterface
    {
        $customer = $this->entityManager()->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');

        return $customer;
    }

    private function entityManager(): EntityManagerInterface
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }
}
