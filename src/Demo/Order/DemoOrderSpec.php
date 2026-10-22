<?php

declare(strict_types=1);

namespace App\Demo\Order;

final readonly class DemoOrderSpec
{
    /** Delivery site other than the default one (Lyon). */
    public const SITE_SAINT_PRIEST = 'saint-priest';

    /** Third delivery site of Acme: the company address book (CompanyAddressDemoFixture) names it "Atelier de Grenoble". */
    public const SITE_GRENOBLE = 'grenoble';

    /** @var array<string, array{street: string, city: string, postcode: string}> the addresses of the company address book, by site */
    private const ADDRESSES = [
        self::SITE_SAINT_PRIEST => ['street' => '45 boulevard des Expositions', 'city' => 'Saint-Priest', 'postcode' => '69800'],
        self::SITE_GRENOBLE => ['street' => '8 avenue des Martyrs', 'city' => 'Grenoble', 'postcode' => '38000'],
    ];

    private const DEFAULT_ADDRESS = ['street' => '12 rue de l\'Industrie', 'city' => 'Lyon', 'postcode' => '69003'];

    /** @param array<string, positive-int> $items quantity by variant code */
    public function __construct(
        public string $customerEmail,
        public int $daysAgo,
        public array $items,
        public string $poReference,
        public OrderScenario $scenario,
        public ?string $costCenter = null,
        public ?string $approverEmail = null,
        public ?string $paymentMethod = null,
        public ?string $deliverySite = null,
    ) {
    }

    /** @return array{street: string, city: string, postcode: string} */
    public static function addressOf(?string $site): array
    {
        return null === $site ? self::DEFAULT_ADDRESS : (self::ADDRESSES[$site] ?? self::DEFAULT_ADDRESS);
    }
}
