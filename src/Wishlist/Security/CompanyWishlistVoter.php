<?php

declare(strict_types=1);

namespace App\Wishlist\Security;

use App\Entity\Wishlist\CompanyWishlist;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;

/**
 * Members of the company read its lists; the creator and the company admins edit them.
 * Disabled members, members of another company, administrators and visitors are denied.
 *
 * @extends Voter<string, CompanyWishlist>
 */
final class CompanyWishlistVoter extends Voter
{
    public function __construct(private readonly CurrentMemberResolverInterface $currentMemberResolver)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof CompanyWishlist &&
            \in_array($attribute, [CompanyWishlistPermissions::VIEW, CompanyWishlistPermissions::EDIT], true);
    }

    /** @param CompanyWishlist $subject */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, mixed $vote = null): bool
    {
        $member = $this->currentMemberResolver->getMember($token);
        $company = $member?->getCompany();
        if (null === $member || null === $company || !$member->isActive() || $company->getId() !== $subject->getCompany()->getId()) {
            return false;
        }

        if (CompanyWishlistPermissions::VIEW === $attribute) {
            return true;
        }

        return $member->isAdmin() ||
            (null !== $subject->getCreatedBy() && $subject->getCreatedBy()->getId() === $member->getCustomer()?->getId());
    }
}
