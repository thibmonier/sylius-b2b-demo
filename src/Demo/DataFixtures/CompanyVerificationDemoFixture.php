<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyVerificationPlugin\Entity\CompanyVerification;
use Tmonier\SyliusCompanyVerificationPlugin\Register\RegisterResult;
use Tmonier\SyliusCompanyVerificationPlugin\Status\RegisterStatus;
use Tmonier\SyliusCompanyVerificationPlugin\Status\VatStatus;
use Tmonier\SyliusCompanyVerificationPlugin\Vies\VatResult;

/**
 * Sprint 55 (T-TECH-55-03): the verification result of Acme Industries (company-verification plugin), recorded as a verified company:
 * register active and VAT number valid. The result is written directly: no service is called while the fixtures load (the demo
 * identifiers are fictitious, the real services would not know them), and the name and address are those of the fictitious company.
 *
 * The companies fixture queued one VerifyCompany message per company on the "async" transport; they are dropped here, last of the suite,
 * so that the consumer of the crontab does not ask the real services about fictitious identifiers and replace this state.
 *
 * Depends on: demo_companies (-15). Runs last: priority -95 (after the ERP export, -90).
 */
final class CompanyVerificationDemoFixture extends AbstractFixture
{
    private const COMPANY_CODE = 'ACME_IND';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection,
    ) {
    }

    public function load(array $options): void
    {
        $company = $this->entityManager->getRepository(CompanyInterface::class)->findOneBy(['code' => self::COMPANY_CODE]);
        if (!$company instanceof CompanyInterface) {
            throw new \RuntimeException(\sprintf('Company verification fixture: the company "%s" is not loaded.', self::COMPANY_CODE));
        }

        $this->entityManager->persist(new CompanyVerification(
            $company,
            $company->getRegistrationNumber(),
            $company->getVatNumber(),
            new RegisterResult(RegisterStatus::Active, 'ACME INDUSTRIES (société fictive)', '12 rue de l\'Industrie, 69003 Lyon'),
            new VatResult(VatStatus::Valid, 'ACME INDUSTRIES (société fictive)', '12 rue de l\'Industrie, 69003 Lyon'),
            new \DateTimeImmutable(),
        ));
        $this->entityManager->flush();

        // Only VerifyCompany is routed to "async" (config/packages/messenger.yaml): the whole queue is the fixtures' own messages.
        $this->connection->executeStatement('DELETE FROM messenger_messages WHERE queue_name = :queue', ['queue' => 'default']);
    }

    public function getName(): string
    {
        return 'demo_company_verification';
    }
}
