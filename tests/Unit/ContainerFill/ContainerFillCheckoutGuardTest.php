<?php

declare(strict_types=1);

namespace App\Tests\Unit\ContainerFill;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\Taxon;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;
use Tmonier\SyliusContainerFillPlugin\Checkout\CheckoutCompleteGuardListener;
use Tmonier\SyliusContainerFillPlugin\Checkout\FillOverrideChecker;
use Tmonier\SyliusContainerFillPlugin\Entity\ContainerRuleInterface;
use Tmonier\SyliusContainerFillPlugin\Entity\ProductVariantLogisticsInterface;
use Tmonier\SyliusContainerFillPlugin\Logistics\Units;
use Tmonier\SyliusContainerFillPlugin\Logistics\VariantLogistics;
use Tmonier\SyliusContainerFillPlugin\Logistics\VariantLogisticsProviderInterface;
use Tmonier\SyliusContainerFillPlugin\Planner\LoadPlannerInterface;
use Tmonier\SyliusContainerFillPlugin\Repository\ContainerRuleRepositoryInterface;
use Tmonier\SyliusContainerFillPlugin\Rule\CartFillChecker;
use Tmonier\SyliusContainerFillPlugin\Rule\RuleEvaluator;
use Tmonier\SyliusContainerFillPlugin\Rule\ScopeMatcher;
use Tmonier\SyliusContainerFillPlugin\Rule\SuggestionFinder;

/**
 * Fix I1: the "complete" guard of container-fill must not block a cart without pallet goods.
 *
 * The plugin plans every variant of the cart, so a rule whose scope is "all products" would put
 * a plain office-supply line in its scope (incomplete logistics data) and block the checkout.
 * The demo therefore scopes its rules to a taxon (see the plugin demo fixture); this test locks
 * that behaviour with the real checker and guard.
 */
final class ContainerFillCheckoutGuardTest extends TestCase
{
    public function testCartWithoutPalletGoodsIsNotBlocked(): void
    {
        $event = $this->guard($this->cart('PEN-BLUE', 'OFFICE', 3));

        self::assertFalse($event->isBlocked());
    }

    public function testCartWithPalletGoodsBreakingTheCaseMultipleIsStillBlocked(): void
    {
        $logistics = $this->createStub(ProductVariantLogisticsInterface::class);
        $logistics->method('isCaseMultipleRequired')->willReturn(true);
        $logistics->method('getUnitsPerCase')->willReturn(6);

        $event = $this->guard($this->cart('WATER', 'PALLET', 5), ['WATER' => $logistics]);

        self::assertTrue($event->isBlocked());
    }

    /** @param array<string, ProductVariantLogisticsInterface> $logisticsByVariant */
    private function guard(Order $order, array $logisticsByVariant = []): GuardEvent
    {
        $taxon = new Taxon();
        $taxon->setCode('PALLET');
        $rule = $this->createStub(ContainerRuleInterface::class);
        $rule->method('getScope')->willReturn(ContainerRuleInterface::SCOPE_TAXON);
        $rule->method('getTaxon')->willReturn($taxon);

        $rules = $this->createStub(ContainerRuleRepositoryInterface::class);
        $rules->method('findEnabledByChannel')->willReturn([$rule]);

        $provider = new class($logisticsByVariant) implements VariantLogisticsProviderInterface {
            /** @param array<string, ProductVariantLogisticsInterface> $logistics */
            public function __construct(private readonly array $logistics)
            {
            }

            public function getForVariants(array $variants): array
            {
                $result = [];
                foreach ($variants as $variant) {
                    $data = $this->getForVariant($variant);
                    $result[$data->key()] = $data;
                }

                return $result;
            }

            public function getForVariant(ProductVariantInterface $variant): VariantLogistics
            {
                return new VariantLogistics($variant, $this->logistics[(string) $variant->getCode()] ?? null, [], new Units());
            }
        };

        $planner = $this->createStub(LoadPlannerInterface::class);
        $evaluator = new RuleEvaluator();
        $checker = new CartFillChecker($rules, $provider, $planner, $evaluator, new SuggestionFinder($planner, $evaluator), new ScopeMatcher());
        $overrides = new FillOverrideChecker(new TokenStorage(), $this->createStub(AuthorizationCheckerInterface::class), null);

        $event = new GuardEvent($order, new Marking(), new Transition('complete', 'payment_selected', 'completed'));
        (new CheckoutCompleteGuardListener($checker, $overrides))($event);

        return $event;
    }

    private function cart(string $variantCode, string $taxonCode, int $quantity): Order
    {
        $taxon = new Taxon();
        $taxon->setCode($taxonCode);
        $product = new Product();
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName($variantCode);
        $product->setMainTaxon($taxon);
        $variant = new ProductVariant();
        $variant->setCurrentLocale('en_US');
        $variant->setFallbackLocale('en_US');
        $variant->setCode($variantCode);
        $variant->setProduct($product);
        $item = new OrderItem();
        $item->setVariant($variant);
        for ($i = 0; $i < $quantity; ++$i) {
            $item->addUnit(new OrderItemUnit($item));
        }

        $order = new Order();
        $order->setChannel(new Channel());
        $order->addItem($item);

        return $order;
    }
}
