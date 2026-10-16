<?php

declare(strict_types=1);

namespace App\Entity\Wishlist;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\WishlistPlugin\Entity\Wishlist;
use Sylius\WishlistPlugin\Entity\WishlistInterface;
use Symfony\Component\Uid\Uuid;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;

/**
 * Makes an official wishlist (items, quantities) the shared list of a company (US-024, ADR-0012).
 *
 * The wishlist keeps no ShopUser and a random token: it never appears in anybody's personal lists, and the
 * personal lists of the plugin are unchanged. Reading goes through the company, editing through the voter.
 */
#[ORM\Entity]
#[ORM\Table(name: 'app_company_wishlist')]
class CompanyWishlist
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: CompanyInterface::class)]
        #[ORM\JoinColumn(name: 'company_id', nullable: false, onDelete: 'CASCADE')]
        private CompanyInterface $company,
        #[ORM\OneToOne(targetEntity: Wishlist::class, cascade: ['persist', 'remove'])]
        #[ORM\JoinColumn(name: 'wishlist_id', nullable: false, unique: true, onDelete: 'CASCADE')]
        private WishlistInterface $wishlist,
        #[ORM\ManyToOne(targetEntity: CustomerInterface::class)]
        #[ORM\JoinColumn(name: 'created_by_id', nullable: true, onDelete: 'SET NULL')]
        private ?CustomerInterface $createdBy = null,
    ) {
        $this->id = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
    }

    /** Non-guessable identifier: it is the one used in the URLs. */
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCompany(): CompanyInterface
    {
        return $this->company;
    }

    public function getWishlist(): WishlistInterface
    {
        return $this->wishlist;
    }

    public function getName(): string
    {
        return $this->wishlist->getName() ?? '';
    }

    public function getCreatedBy(): ?CustomerInterface
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
