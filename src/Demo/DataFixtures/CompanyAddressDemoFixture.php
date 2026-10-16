<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyAddressInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;

/**
 * Shared address book of Acme (US-025): the sites Isabelle (admin) manages and Claire (buyer) picks at the
 * checkout. Built with the plugin's factory and checked with its validation rules, so an invalid demo address
 * fails the fixture load instead of breaking the checkout picker.
 */
final class CompanyAddressDemoFixture extends AbstractFixture
{
    private const COMPANY_CODE = 'ACME_IND';

    /** @var array<string, array{first_name: string, last_name: string, street: string, postcode: string, city: string}> */
    private const ADDRESSES = [
        'Usine de Lyon' => ['first_name' => 'Claire', 'last_name' => 'Dupont', 'street' => '12 rue de l\'Industrie', 'postcode' => '69003', 'city' => 'Lyon'],
        'Entrepôt de Saint-Priest' => ['first_name' => 'Paul', 'last_name' => 'Martin', 'street' => '45 boulevard des Expositions', 'postcode' => '69800', 'city' => 'Saint-Priest'],
        'Atelier de Grenoble' => ['first_name' => 'Isabelle', 'last_name' => 'Renard', 'street' => '8 avenue des Martyrs', 'postcode' => '38000', 'city' => 'Grenoble'],
    ];

    /**
     * @param FactoryInterface<CompanyAddressInterface> $companyAddressFactory
     * @param FactoryInterface<AddressInterface>        $addressFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_company_accounts.factory.company_address')]
        private readonly FactoryInterface $companyAddressFactory,
        #[Autowire(service: 'sylius.factory.address')]
        private readonly FactoryInterface $addressFactory,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function load(array $options): void
    {
        $company = $this->entityManager->getRepository(CompanyInterface::class)->findOneBy(['code' => self::COMPANY_CODE]);
        if (!$company instanceof CompanyInterface) {
            return;
        }

        $existing = $this->existingLabels($company);
        foreach (self::ADDRESSES as $label => $fields) {
            if (!in_array($label, $existing, true)) {
                $this->entityManager->persist($this->companyAddress($company, $label, $fields));
            }
        }
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_company_addresses';
    }

    /**
     * @param array{first_name: string, last_name: string, street: string, postcode: string, city: string} $fields
     */
    private function companyAddress(CompanyInterface $company, string $label, array $fields): CompanyAddressInterface
    {
        $address = $this->addressFactory->createNew();
        $address->setFirstName($fields['first_name']);
        $address->setLastName($fields['last_name']);
        $address->setCompany((string) $company->getName());
        $address->setStreet($fields['street']);
        $address->setPostcode($fields['postcode']);
        $address->setCity($fields['city']);
        $address->setCountryCode('FR');

        $companyAddress = $this->companyAddressFactory->createNew();
        $companyAddress->setCompany($company);
        $companyAddress->setLabel($label);
        $companyAddress->setAddress($address);

        $violations = $this->validator->validate($companyAddress, null, ['sylius']);
        if (0 < count($violations)) {
            throw new \LogicException(sprintf('Demo company address "%s" is invalid: %s', $label, (string) $violations));
        }

        return $companyAddress;
    }

    /**
     * @return list<string>
     */
    private function existingLabels(CompanyInterface $company): array
    {
        return array_map(
            static fn (CompanyAddressInterface $companyAddress): string => (string) $companyAddress->getLabel(),
            $this->entityManager->getRepository(CompanyAddressInterface::class)->findBy(['company' => $company]),
        );
    }
}
