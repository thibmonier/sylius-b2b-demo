<?php

declare(strict_types=1);

namespace App\Demo\Account;

/** A demo login (shop member, B2C shopper or back-office user). */
final readonly class DemoAccount
{
    public function __construct(
        public string $email,
        public string $password,
        public string $firstName,
        public string $lastName,
        public string $role,
        public bool $enabled = true,
        public ?string $companyName = null,
    ) {
    }

    public function fullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /** Bilingual role label (FR / EN). */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'buyer' => 'Acheteur / Buyer',
            'approver' => 'Approbateur / Approver',
            'admin' => 'Administrateur / Admin',
            'consumer' => 'Client B2C / B2C customer',
            'back_office' => 'Admin back-office (restreint) / restricted back-office admin',
            default => $this->role,
        };
    }
}
