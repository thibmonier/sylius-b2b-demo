<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\TwigHooks\Hookable\Checker\HookableConditionCheckerInterface;
use Sylius\TwigHooks\Registry\HookablesRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * US-019 — The pieces of the other plugins that show prices on the product page (structured data, quantity matrix)
 * are hookables with the condition of the channel access plugin: asserted on the condition as the page evaluates it,
 * for an anonymous B2B visitor, a signed-in customer and an anonymous B2C visitor. (The demo catalogue has no
 * product with options, so no page renders the matrix: the hookable is checked directly.)
 */
final class HiddenPriceHookablesTest extends WebTestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function priceHookables(): iterable
    {
        yield 'JSON-LD Product/Offer (agentic feeds)' => ['sylius_shop.product.show', 'tmonier_agentic_feeds_json_ld'];
        yield 'quantity matrix (quick order)' => ['sylius_shop.product.show.content.info.summary', 'tmonier_quick_order_matrix'];
    }

    #[DataProvider('priceHookables')]
    public function testAnAnonymousB2bVisitorDoesNotGetTheHookable(string $hook, string $name): void
    {
        self::bootKernel();
        $this->visit(DemoChannelContext::B2B);

        self::assertFalse($this->enabled($hook, $name));
    }

    #[DataProvider('priceHookables')]
    public function testASignedInCustomerGetsIt(string $hook, string $name): void
    {
        self::bootKernel();
        $this->visit(DemoChannelContext::B2B);
        $this->signIn('claire@acme.example');

        self::assertTrue($this->enabled($hook, $name));
    }

    #[DataProvider('priceHookables')]
    public function testAnAnonymousB2cVisitorGetsIt(string $hook, string $name): void
    {
        self::bootKernel();
        $this->visit(DemoChannelContext::B2C);

        self::assertTrue($this->enabled($hook, $name));
    }

    private function visit(string $channel): void
    {
        $request = Request::create('/fr_FR/products/x', cookies: [DemoChannelContext::COOKIE => $channel]);
        $requests = self::getContainer()->get('request_stack');
        self::assertInstanceOf(RequestStack::class, $requests);
        $requests->push($request);
    }

    private function signIn(string $email): void
    {
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);
        $id = $connection->fetchOne('SELECT u.id FROM sylius_shop_user u JOIN sylius_customer c ON c.id = u.customer_id WHERE c.email = ?', [$email]);
        $repository = self::getContainer()->get('sylius.repository.shop_user');
        self::assertInstanceOf(RepositoryInterface::class, $repository);
        $user = $repository->find($id);
        self::assertInstanceOf(ShopUserInterface::class, $user);
        $storage = self::getContainer()->get('security.token_storage');
        self::assertInstanceOf(TokenStorageInterface::class, $storage);
        $storage->setToken(new UsernamePasswordToken($user, 'shop', $user->getRoles()));
    }

    private function enabled(string $hook, string $name): bool
    {
        $registry = self::getContainer()->get('sylius_twig_hooks.registry.hookables');
        self::assertInstanceOf(HookablesRegistry::class, $registry);
        $checker = self::getContainer()->get(HookableConditionCheckerInterface::class);
        self::assertInstanceOf(HookableConditionCheckerInterface::class, $checker);

        foreach ($registry->getEnabledFor($hook) as $hookable) {
            if ($hookable->name === $name) {
                return $checker->isEnabled($hookable, []);
            }
        }
        self::fail(\sprintf('The hookable "%s" of the hook "%s" is not registered.', $name, $hook));
    }
}
