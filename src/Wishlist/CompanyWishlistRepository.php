<?php

declare(strict_types=1);

namespace App\Wishlist;

use App\Entity\Wishlist\CompanyWishlist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;

/** Reads the company lists with their items in one query (no N+1 on items, products or variants). */
class CompanyWishlistRepository
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @return list<CompanyWishlist> */
    public function findByCompany(CompanyInterface $company): array
    {
        /** @var list<CompanyWishlist> $lists */
        $lists = $this->baseQuery()
            ->andWhere('cw.company = :company')
            ->setParameter('company', $company)
            ->orderBy('w.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $lists;
    }

    public function find(string $id): ?CompanyWishlist
    {
        if (!Uuid::isValid($id)) {
            return null;
        }

        /** @var CompanyWishlist|null $list */
        $list = $this->baseQuery()
            ->andWhere('cw.id = :id')
            ->setParameter('id', Uuid::fromString($id), UuidType::NAME)
            ->getQuery()
            ->getOneOrNullResult();

        return $list;
    }

    private function baseQuery(): \Doctrine\ORM\QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('cw', 'w', 'wp', 'p', 'v')
            ->from(CompanyWishlist::class, 'cw')
            ->innerJoin('cw.wishlist', 'w')
            ->leftJoin('w.wishlistProducts', 'wp')
            ->leftJoin('wp.product', 'p')
            ->leftJoin('wp.variant', 'v');
    }
}
