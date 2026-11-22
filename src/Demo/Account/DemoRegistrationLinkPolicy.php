<?php

declare(strict_types=1);

namespace App\Demo\Account;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusIndustryTradeTheme\Shop\RegistrationLinkPolicyInterface;

/**
 * The registration rule of the demo (US-162): the "Register" link is hidden when the registration routes answer 403
 * (same rule as RegistrationGateListener, US-040). Read by the published theme on a themed channel (theme 0.3.2) and by
 * the Twig function demo_registration_open() everywhere else.
 */
final readonly class DemoRegistrationLinkPolicy implements RegistrationLinkPolicyInterface
{
    public function __construct(
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        #[Autowire(param: 'demo.registration_open')]
        private bool $registrationOpen,
    ) {
    }

    public function isVisible(): bool
    {
        return !$this->demoMode || $this->registrationOpen;
    }
}
