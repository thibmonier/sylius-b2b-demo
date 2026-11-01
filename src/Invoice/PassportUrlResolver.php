<?php

declare(strict_types=1);

namespace App\Invoice;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;
use Tmonier\SyliusFacturXPlugin\Mapper\PassportUrlResolverInterface;

final readonly class PassportUrlResolver implements PassportUrlResolverInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function resolve(string $productCode): ?string
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from(DigitalProductPassport::class, 'p')
            ->join('p.productVariant', 'v')
            ->where('v.code = :code')
            ->setParameter('code', $productCode);

        $passport = $qb->getQuery()->getOneOrNullResult();

        if (!$passport instanceof DigitalProductPassport || !$passport->isPublished()) {
            return null;
        }

        return $this->urlGenerator->generate(
            'tmonier_digital_product_passport_show',
            ['id' => $passport->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
