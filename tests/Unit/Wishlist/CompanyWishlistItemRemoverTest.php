<?php

declare(strict_types=1);

namespace App\Tests\Unit\Wishlist;

use App\Entity\Wishlist\CompanyWishlist;
use App\Wishlist\CompanyWishlistItemRemover;
use App\Wishlist\CompanyWishlistWriteFailed;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\WishlistPlugin\Entity\WishlistInterface;
use Sylius\WishlistPlugin\Entity\WishlistProductInterface;

final class CompanyWishlistItemRemoverTest extends TestCase
{
    public function testItRemovesTheItemWithASingleFlush(): void
    {
        $item = $this->item(7);
        $wishlist = $this->wishlist($item, $this->item(8));
        $wishlist->expects(self::once())->method('removeProduct')->with($item);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        self::assertTrue((new CompanyWishlistItemRemover($entityManager))->remove($this->list($wishlist), 7));
    }

    public function testItWritesNothingWhenTheItemIsNotInTheList(): void
    {
        $wishlist = $this->wishlist($this->item(8));
        $wishlist->expects(self::never())->method('removeProduct');
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        self::assertFalse((new CompanyWishlistItemRemover($entityManager))->remove($this->list($wishlist), 7));
    }

    public function testAFailedWriteClearsTheUnitOfWorkAndIsReported(): void
    {
        $wishlist = $this->wishlist($this->item(7));
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('flush')->willThrowException(new \RuntimeException('database down'));
        $entityManager->expects(self::once())->method('clear');

        $this->expectException(CompanyWishlistWriteFailed::class);

        (new CompanyWishlistItemRemover($entityManager))->remove($this->list($wishlist), 7);
    }

    private function item(int $id): WishlistProductInterface
    {
        $item = $this->createStub(WishlistProductInterface::class);
        $item->method('getId')->willReturn($id);

        return $item;
    }

    private function wishlist(WishlistProductInterface ...$items): WishlistInterface&\PHPUnit\Framework\MockObject\MockObject
    {
        $wishlist = $this->createMock(WishlistInterface::class);
        $wishlist->method('getWishlistProducts')->willReturn(new ArrayCollection($items));

        return $wishlist;
    }

    private function list(WishlistInterface $wishlist): CompanyWishlist
    {
        $list = $this->createStub(CompanyWishlist::class);
        $list->method('getWishlist')->willReturn($wishlist);

        return $list;
    }
}
