<?php

declare(strict_types=1);

namespace App\Demo\Account;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Exposes the demo mode switch and the demo accounts to the demo templates (US-015). */
final class DemoTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly DemoAccountCatalog $catalog,
        #[Autowire(param: 'demo.mode')]
        private readonly bool $demoMode,
        #[Autowire(param: 'demo.mailpit_url')]
        private readonly string $mailpitUrl,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('demo_mode', fn (): bool => $this->demoMode),
            new TwigFunction('demo_mailpit_url', fn (): string => $this->mailpitUrl),
            new TwigFunction('demo_shop_accounts', $this->catalog->shopAccounts(...)),
            new TwigFunction('demo_admin_accounts', $this->catalog->adminUsers(...)),
        ];
    }
}
