<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Admin\DemoAdminRole;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Restricts the public back-office accounts (US-035): every admin user of the demo account catalogue
 * (config/demo/accounts.yaml, single source) receives ROLE_DEMO_ADMIN, which DemoAdminWriteGuard enforces.
 * No other back-office account is touched.
 *
 * Depends on: admin_user (0)
 * Priority: -1
 */
final class DemoAdminRoleFixture extends AbstractFixture
{
    /**
     * @param RepositoryInterface<AdminUserInterface> $adminUserRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DemoAccountCatalog $catalog,
        #[Autowire(service: 'sylius.repository.admin_user')]
        private readonly RepositoryInterface $adminUserRepository,
    ) {
    }

    public function load(array $options): void
    {
        foreach ($this->catalog->adminUsers() as $account) {
            $user = $this->adminUserRepository->findOneBy(['emailCanonical' => mb_strtolower($account->email)]);
            if ($user instanceof AdminUserInterface && !$user->hasRole(DemoAdminRole::NAME)) {
                $user->addRole(DemoAdminRole::NAME);
            }
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_admin_role';
    }
}
