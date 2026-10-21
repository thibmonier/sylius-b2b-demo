<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Journey;

use App\Demo\Journey\CartScenarioPreparer;
use App\Demo\Journey\ScenarioListResolver;
use App\Demo\Journey\ScenarioPreparationFailed;
use App\Entity\Wishlist\CompanyWishlist;
use App\Wishlist\CompanyWishlistCartAdder;
use App\Wishlist\CompanyWishlistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\WishlistPlugin\Entity\WishlistInterface;
use Sylius\WishlistPlugin\Entity\WishlistProductInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;

final class CartScenarioPreparerTest extends TestCase
{
    public function testTheCartOfTheVisitorIsKeptWhenNoLineOfTheListCanBeAdded(): void
    {
        $cart = new Order();
        $line = new OrderItem();
        $cart->addItem($line);

        $adder = $this->createMock(CompanyWishlistCartAdder::class);
        $adder->method('add')->willReturn(['added' => 0, 'skipped' => 1]);

        $preparer = new CartScenarioPreparer($this->resolverOfOneItem(), $adder, $this->cartContext($cart));

        try {
            $preparer->prepare([]);
            self::fail('A list without any addable line fails the preparation.');
        } catch (ScenarioPreparationFailed) {
        }

        self::assertSame([$line], $cart->getItems()->toArray(), 'The cart of the visitor is not emptied by a failed launch.');
    }

    public function testTheCartIsReplacedByTheLinesOfTheList(): void
    {
        $cart = new Order();
        $cart->addItem(new OrderItem());

        $adder = $this->createMock(CompanyWishlistCartAdder::class);
        $adder->expects(self::once())->method('add')->willReturnCallback(static function () use ($cart): array {
            self::assertCount(0, $cart->getItems(), 'The previous lines are gone before the list is added.');

            return ['added' => 1, 'skipped' => 0];
        });

        $preparer = new CartScenarioPreparer($this->resolverOfOneItem(), $adder, $this->cartContext($cart));
        $preparer->prepare([]);
    }

    private function resolverOfOneItem(): ScenarioListResolver
    {
        $wishlist = $this->createStub(WishlistInterface::class);
        $wishlist->method('getName')->willReturn('Atelier');
        $wishlist->method('getWishlistProducts')->willReturn(new ArrayCollection([$this->createStub(WishlistProductInterface::class)]));
        $list = $this->createStub(CompanyWishlist::class);
        $list->method('getWishlist')->willReturn($wishlist);
        $lists = $this->createStub(CompanyWishlistRepository::class);
        $lists->method('findByCompany')->willReturn([$list]);
        $member = $this->createStub(CompanyMemberInterface::class);
        $member->method('getCompany')->willReturn($this->createStub(CompanyInterface::class));
        $members = $this->createStub(CurrentMemberResolverInterface::class);
        $members->method('getMember')->willReturn($member);

        return new ScenarioListResolver($members, $lists);
    }

    private function cartContext(Order $cart): CartContextInterface
    {
        $context = $this->createStub(CartContextInterface::class);
        $context->method('getCart')->willReturn($cart);

        return $context;
    }
}
