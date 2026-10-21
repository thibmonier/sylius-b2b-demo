<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use App\Wishlist\CompanyWishlistCartAdder;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/** "Cart" scenario: the cart of the visitor is emptied then filled with the lines of a purchase list of their company. */
#[AutoconfigureTag(ScenarioPreparer::TAG, ['key' => 'cart'])]
final readonly class CartScenarioPreparer implements ScenarioPreparer
{
    public function __construct(
        private ScenarioListResolver $lists,
        private CompanyWishlistCartAdder $cartAdder,
        private CartContextInterface $carts,
    ) {
    }

    public function prepare(array $options): void
    {
        $items = $this->lists->items($options);

        // Replayable: a second click gives the same cart, not doubled quantities.
        // The previous lines are only removed in memory: the adder flushes when it adds a line, and when it adds none
        // they are put back, so a failed launch never empties the visitor's cart.
        $cart = $this->carts->getCart();
        $previous = $cart instanceof OrderInterface ? $cart->getItems()->toArray() : [];
        if ($cart instanceof OrderInterface) {
            $cart->clearItems();
        }

        $result = $this->cartAdder->add($items);
        if (0 === $result['added']) {
            foreach ($previous as $item) {
                $cart->addItem($item);
            }

            throw new ScenarioPreparationFailed('No line of the list could be added to the cart.');
        }
    }
}
