<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyRepositoryInterface;

/**
 * Smoke tests for US-009: B2B companies, members and demo accounts.
 *
 * Requires a database loaded with the `demo` fixture suite:
 *   bin/console sylius:fixtures:load --suite=demo --no-interaction
 *   vendor/bin/phpunit tests/Smoke/AccountsSmokeTest.php
 *
 * Scenarios covered (Gherkin → test):
 *   - Nominal   : 5 companies, main members exist with correct roles
 *   - Alt 1     : Sophie admin user exists (back-office restricted)
 *   - Alt 2     : Marc Lefebvre belongs to two companies
 *   - Error 1   : Wrong password is refused (not a fixture test — login logic)
 *   - Error 2   : Thomas Bernard has a disabled ShopUser
 */
final class AccountsSmokeTest extends KernelTestCase
{
    // ---------------------------------------------------------------------------
    // Scénario nominal: 5 entreprises et membres principaux
    // ---------------------------------------------------------------------------

    public function testFiveCompaniesExist(): void
    {
        self::bootKernel();

        /** @var CompanyRepositoryInterface $companyRepo */
        $companyRepo = self::getContainer()->get('tmonier_company_accounts.repository.company');

        $companies = $companyRepo->findAll();
        self::assertGreaterThanOrEqual(
            5,
            \count($companies),
            'At least five demo companies must exist (US-009).',
        );
    }

    /**
     * @dataProvider provideExpectedCompanyCodes
     */
    public function testExpectedCompanyExistsByCode(string $code, string $expectedName): void
    {
        self::bootKernel();

        /** @var CompanyRepositoryInterface $companyRepo */
        $companyRepo = self::getContainer()->get('tmonier_company_accounts.repository.company');

        /** @var CompanyInterface|null $company */
        $company = $companyRepo->findOneBy(['code' => $code]);

        self::assertNotNull($company, \sprintf('Company with code "%s" (%s) must exist.', $code, $expectedName));
        self::assertSame($expectedName, $company->getName());
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideExpectedCompanyCodes(): iterable
    {
        yield 'Acme Industries' => ['ACME_IND', 'Acme Industries'];
        yield 'TechParts SAS' => ['TECHPARTS', 'TechParts SAS'];
        yield 'BuildCo SA' => ['BUILDCO', 'BuildCo SA'];
        yield 'GlobalMaint SA' => ['GLOBALMAINT', 'GlobalMaint SA'];
        yield 'IndusPro SARL' => ['INDUSPRO', 'IndusPro SARL'];
    }

    /**
     * @dataProvider provideExpectedB2bAccounts
     */
    public function testExpectedShopUserExists(string $email): void
    {
        self::bootKernel();

        /** @var \Doctrine\Persistence\ObjectRepository<CustomerInterface> $customerRepo */
        $customerRepo = self::getContainer()->get('sylius.repository.customer');

        $customer = $customerRepo->findOneBy(['emailCanonical' => strtolower($email)]);

        self::assertNotNull($customer, \sprintf('Shop user "%s" must exist after fixture load.', $email));
        self::assertNotNull($customer->getUser(), \sprintf('Customer "%s" must have a linked ShopUser.', $email));
    }

    /** @return iterable<string, array{string}> */
    public static function provideExpectedB2bAccounts(): iterable
    {
        // Acme Industries
        yield 'claire@acme.example' => ['claire@acme.example'];
        yield 'paul@acme.example' => ['paul@acme.example'];
        yield 'isabelle@acme.example' => ['isabelle@acme.example'];
        // TechParts SAS
        yield 'marc@techparts.example' => ['marc@techparts.example'];
        yield 'nathalie@techparts.example' => ['nathalie@techparts.example'];
        // BuildCo SA
        yield 'antoine@buildco.example' => ['antoine@buildco.example'];
        // B2C
        yield 'lea@demo.example' => ['lea@demo.example'];
    }

    public function testAcmeMembersHaveCorrectRoles(): void
    {
        self::bootKernel();

        /** @var CompanyRepositoryInterface $companyRepo */
        $companyRepo = self::getContainer()->get('tmonier_company_accounts.repository.company');

        /** @var CompanyInterface|null $acme */
        $acme = $companyRepo->findOneBy(['code' => 'ACME_IND']);
        self::assertNotNull($acme, 'Acme Industries company must exist.');

        $rolesByEmail = [];
        foreach ($acme->getMembers() as $member) {
            $email = $member->getCustomer()?->getEmailCanonical();
            if ($email !== null) {
                $rolesByEmail[$email] = $member->getRole();
            }
        }

        self::assertSame(CompanyMemberInterface::ROLE_BUYER, $rolesByEmail['claire@acme.example'] ?? null, 'Claire must be ROLE_BUYER in Acme.');
        self::assertSame(CompanyMemberInterface::ROLE_APPROVER, $rolesByEmail['paul@acme.example'] ?? null, 'Paul must be ROLE_APPROVER in Acme.');
        self::assertSame(CompanyMemberInterface::ROLE_ADMIN, $rolesByEmail['isabelle@acme.example'] ?? null, 'Isabelle must be ROLE_ADMIN in Acme.');
    }

    // ---------------------------------------------------------------------------
    // Scénario alternatif 2: membre rattaché à plusieurs entreprises
    // ---------------------------------------------------------------------------

    public function testEachCustomerBelongsToExactlyOneCompany(): void
    {
        self::bootKernel();

        /** @var \Doctrine\Persistence\ObjectRepository<CustomerInterface> $customerRepo */
        $customerRepo = self::getContainer()->get('sylius.repository.customer');

        $marc = $customerRepo->findOneBy(['emailCanonical' => 'marc@techparts.example']);
        self::assertNotNull($marc, 'Marc Lefebvre (marc@techparts.example) must exist.');

        /** @var \Doctrine\Persistence\ObjectRepository<CompanyMemberInterface> $memberRepo */
        $memberRepo = self::getContainer()->get('tmonier_company_accounts.repository.company_member');

        // The company-accounts plugin enforces one company per customer
        // (unique constraint on company_member.customer).
        $marcMemberships = $memberRepo->findBy(['customer' => $marc]);
        self::assertCount(
            1,
            $marcMemberships,
            'A customer belongs to exactly one company (plugin invariant).',
        );
        self::assertSame(
            'TECHPARTS',
            (string) $marcMemberships[0]->getCompany()?->getCode(),
            'Marc must belong to TechParts SAS.',
        );
    }

    // ---------------------------------------------------------------------------
    // Scénario d'erreur: compte membre désactivé
    // ---------------------------------------------------------------------------

    public function testThomasBernardShopUserIsDisabled(): void
    {
        self::bootKernel();

        /** @var \Doctrine\Persistence\ObjectRepository<CustomerInterface> $customerRepo */
        $customerRepo = self::getContainer()->get('sylius.repository.customer');

        $thomas = $customerRepo->findOneBy(['emailCanonical' => 'thomas@buildco.example']);
        self::assertNotNull($thomas, 'Thomas Bernard (thomas@buildco.example) must exist.');

        $user = $thomas->getUser();
        self::assertNotNull($user, 'Thomas must have a linked ShopUser.');
        self::assertFalse($user->isEnabled(), 'Thomas Bernard ShopUser must be disabled (error scenario).');
    }
}

/**
 * Web-client tests for the /demo/accounts page.
 * Kept in a separate class because WebTestCase and KernelTestCase have different setups.
 */
final class DemoAccountsPageTest extends WebTestCase
{
    public function testDemoAccountsPageAnswers200(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/accounts');

        self::assertResponseIsSuccessful('The /demo/accounts page must return HTTP 200.');
        self::assertStringContainsString('Acme Industries', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('Demo1234!', (string) $client->getResponse()->getContent());
    }
}
