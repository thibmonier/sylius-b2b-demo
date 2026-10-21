<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use App\Wishlist\CompanyWishlistCartAdder;
use Doctrine\ORM\EntityManagerInterface;
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
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function prepare(array $options): void
    {
        $items = $this->lists->items($options);

        // Replayable: a second click gives the same cart, not doubled quantities.
        $cart = $this->carts->getCart();
        if ($cart instanceof OrderInterface) {
            $cart->clearItems();
            $this->entityManager->flush();
        }

        $result = $this->cartAdder->add($items);
        if (0 === $result['added']) {
            throw new ScenarioPreparationFailed('No line of the list could be added to the cart.');
        }
    }
}
