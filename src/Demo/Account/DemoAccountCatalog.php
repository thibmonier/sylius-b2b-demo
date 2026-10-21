<?php

declare(strict_types=1);

namespace App\Demo\Account;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Single source of truth for the demo accounts (US-015). The data lives in
 * config/demo/accounts.yaml; fixtures, /demo/accounts and the login pages all read it here.
 */
final class DemoAccountCatalog
{
    /**
     * @param list<array<string, mixed>> $companies
     * @param list<array<string, mixed>> $consumers
     * @param list<array<string, mixed>> $adminUsers
     */
    public function __construct(
        #[Autowire(param: 'demo.password')]
        private readonly string $password,
        #[Autowire(param: 'demo.companies')]
        private readonly array $companies,
        #[Autowire(param: 'demo.consumers')]
        private readonly array $consumers,
        #[Autowire(param: 'demo.admin_users')]
        private readonly array $adminUsers,
    ) {
        $this->assertUniqueEmails();
    }

    public function password(): string
    {
        return $this->password;
    }

    /** @return list<DemoCompany> */
    public function companies(): array
    {
        return array_map(fn (array $company): DemoCompany => new DemoCompany(
            self::string($company, 'code'),
            self::string($company, 'name'),
            isset($company['vat_number']) ? self::string($company, 'vat_number') : null,
            isset($company['registration_number']) ? self::string($company, 'registration_number') : null,
            $this->members($company),
        ), $this->companies);
    }

    /** @return list<DemoAccount> */
    public function consumers(): array
    {
        return array_map(fn (array $consumer): DemoAccount => $this->shopAccount($consumer, 'consumer', null), $this->consumers);
    }

    /** @return list<DemoAccount> */
    public function adminUsers(): array
    {
        return array_map(static fn (array $admin): DemoAccount => new DemoAccount(
            self::string($admin, 'email'),
            self::string($admin, 'password'),
            self::string($admin, 'first_name'),
            self::string($admin, 'last_name'),
            'back_office',
            ($admin['enabled'] ?? true) === true,
        ), $this->adminUsers);
    }

    /** @return list<DemoAccount> every storefront login: company members then B2C shoppers */
    public function shopAccounts(): array
    {
        $accounts = [];
        foreach ($this->companies() as $company) {
            array_push($accounts, ...$company->members);
        }

        return [...$accounts, ...$this->consumers()];
    }

    /** The storefront login with this e-mail (company member or B2C shopper), null when there is none. */
    public function findShopAccount(string $email): ?DemoAccount
    {
        foreach ($this->shopAccounts() as $account) {
            if (0 === strcasecmp($account->email, trim($email))) {
                return $account;
            }
        }

        return null;
    }

    /** Whether the e-mail is one of the storefront logins published on /demo/accounts (shared by every visitor). */
    public function isSharedShopAccount(string $email): bool
    {
        return null !== $this->findShopAccount($email);
    }

    private function assertUniqueEmails(): void
    {
        $seen = [];
        $accounts = [...$this->shopAccounts(), ...$this->adminUsers()];
        foreach ($accounts as $account) {
            if (isset($seen[$account->email])) {
                throw new \InvalidArgumentException(\sprintf('Demo account e-mail "%s" is defined more than once.', $account->email));
            }
            $seen[$account->email] = true;
        }
    }

    /**
     * @param array<string, mixed> $company
     *
     * @return list<DemoAccount>
     */
    private function members(array $company): array
    {
        $name = self::string($company, 'name');
        $accounts = [];
        foreach (self::rows($company['members'] ?? null) as $member) {
            $accounts[] = $this->shopAccount($member, self::string($member, 'role'), $name);
        }

        return $accounts;
    }

    /** @return list<array<string, mixed>> */
    private static function rows(mixed $rows): array
    {
        if (!\is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            if (\is_array($row)) {
                $result[] = array_combine(array_map(strval(...), array_keys($row)), $row);
            }
        }

        return $result;
    }

    /** @param array<string, mixed> $data */
    private function shopAccount(array $data, string $role, ?string $companyName): DemoAccount
    {
        return new DemoAccount(
            self::string($data, 'email'),
            $this->password,
            self::string($data, 'first_name'),
            self::string($data, 'last_name'),
            $role,
            ($data['enabled'] ?? true) === true,
            $companyName,
        );
    }

    /** @param array<string, mixed> $data */
    private static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (!\is_string($value)) {
            throw new \InvalidArgumentException(\sprintf('Demo account key "%s" must be a string.', $key));
        }

        return $value;
    }
}
