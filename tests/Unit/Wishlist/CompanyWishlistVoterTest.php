<?php

declare(strict_types=1);

namespace App\Tests\Unit\Wishlist;

use App\Entity\Wishlist\CompanyWishlist;
use App\Wishlist\Security\CompanyWishlistPermissions;
use App\Wishlist\Security\CompanyWishlistVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\WishlistPlugin\Entity\Wishlist;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;

final class CompanyWishlistVoterTest extends TestCase
{
    private const ACME = 1;

    private const OTHER = 2;

    private const CLAIRE = 10;

    private const PAUL = 11;

    /** @return iterable<string, array{?array{int, int, string, 3?: bool}, string, bool}> */
    public static function provideDecisions(): iterable
    {
        yield 'same company, buyer: reads' => [[self::ACME, self::PAUL, 'buyer'], CompanyWishlistPermissions::VIEW, true];
        yield 'same company, creator: edits' => [[self::ACME, self::CLAIRE, 'buyer'], CompanyWishlistPermissions::EDIT, true];
        yield 'same company, other buyer: cannot edit' => [[self::ACME, self::PAUL, 'buyer'], CompanyWishlistPermissions::EDIT, false];
        yield 'same company, approver: cannot edit' => [[self::ACME, self::PAUL, 'approver'], CompanyWishlistPermissions::EDIT, false];
        yield 'same company, admin: edits' => [[self::ACME, self::PAUL, 'admin'], CompanyWishlistPermissions::EDIT, true];
        yield 'other company, admin: no read' => [[self::OTHER, self::PAUL, 'admin'], CompanyWishlistPermissions::VIEW, false];
        yield 'other company, admin: no edit' => [[self::OTHER, self::PAUL, 'admin'], CompanyWishlistPermissions::EDIT, false];
        yield 'disabled member: no read' => [[self::ACME, self::CLAIRE, 'buyer', false], CompanyWishlistPermissions::VIEW, false];
        yield 'no membership (visitor, admin user): no read' => [null, CompanyWishlistPermissions::VIEW, false];
    }

    /** @param array{int, int, string, 3?: bool}|null $memberSpec */
    #[DataProvider('provideDecisions')]
    public function testDecision(?array $memberSpec, string $attribute, bool $granted): void
    {
        $member = null === $memberSpec ? null : $this->member(...$memberSpec);
        $resolver = $this->createStub(CurrentMemberResolverInterface::class);
        $resolver->method('getMember')->willReturn($member);

        $vote = (new CompanyWishlistVoter($resolver))->vote(new NullToken(), $this->list(), [$attribute]);

        self::assertSame($granted ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED, $vote);
    }

    public function testItAbstainsOnOtherSubjectsAndAttributes(): void
    {
        $voter = new CompanyWishlistVoter($this->createStub(CurrentMemberResolverInterface::class));

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote(new NullToken(), new \stdClass(), [CompanyWishlistPermissions::VIEW]));
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote(new NullToken(), $this->list(), ['OTHER']));
    }

    private function list(): CompanyWishlist
    {
        $company = $this->createStub(CompanyInterface::class);
        $company->method('getId')->willReturn(self::ACME);
        $creator = $this->createStub(CustomerInterface::class);
        $creator->method('getId')->willReturn(self::CLAIRE);

        return new CompanyWishlist($company, new Wishlist(), $creator);
    }

    private function member(int $companyId, int $customerId, string $role, bool $active = true): CompanyMemberInterface
    {
        $company = $this->createStub(CompanyInterface::class);
        $company->method('getId')->willReturn($companyId);
        $customer = $this->createStub(CustomerInterface::class);
        $customer->method('getId')->willReturn($customerId);

        $member = $this->createStub(CompanyMemberInterface::class);
        $member->method('getCompany')->willReturn($company);
        $member->method('getCustomer')->willReturn($customer);
        $member->method('getRole')->willReturn($role);
        $member->method('isAdmin')->willReturn('admin' === $role);
        $member->method('isActive')->willReturn($active);

        return $member;
    }
}
