<?php

declare(strict_types=1);

namespace App\Demo\Site;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** The validated site and contact URLs (null = hidden) for the footer and the demo bar (US-074, US-076), and the call-to-action targets (US-142). */
final class SiteLinksTwigExtension extends AbstractExtension
{
    public function __construct(private readonly SiteLinks $links, private readonly ContactCta $cta)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_demo_site_url', $this->links->siteUrl(...)),
            new TwigFunction('app_demo_contact_url', $this->links->contactUrl(...)),
            new TwigFunction('app_demo_cta_targets', $this->cta->targets(...)),
        ];
    }
}
