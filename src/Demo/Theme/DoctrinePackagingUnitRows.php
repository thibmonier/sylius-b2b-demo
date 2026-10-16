<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class DoctrinePackagingUnitRows implements PackagingUnitRows
{
    /** @param EntityRepository<object> $attributeValueRepository */
    public function __construct(
        #[Autowire(service: 'sylius.repository.product_attribute_value')]
        private readonly EntityRepository $attributeValueRepository,
    ) {
    }

    public function rows(): array
    {
        /** @var list<array{productId: int|string, locale: string, text: string|null}> $rows */
        $rows = $this->attributeValueRepository->createQueryBuilder('v')
            ->select('IDENTITY(v.subject) AS productId', 'v.localeCode AS locale', 'v.text AS text')
            ->innerJoin('v.attribute', 'a')
            ->andWhere('a.code = :code')
            ->andWhere('v.text IS NOT NULL')
            ->setParameter('code', PackagingUnitProvider::ATTRIBUTE_CODE)
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
