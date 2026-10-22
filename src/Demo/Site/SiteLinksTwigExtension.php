<?php

declare(strict_types=1);

namespace App\Demo\Site;

use App\Demo\Vitrine\StandardBlock;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** The validated site and contact URLs (null = hidden) for the footer and the demo bar (US-074, US-076), the call-to-action targets (US-142), and the profile and repository links of the author mention (US-113). */
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
            new TwigFunction('app_demo_profile_url', $this->links->profileUrl(...)),
            new TwigFunction('app_demo_repository_url', static fn (): string => StandardBlock::DEMO_URL),
        ];
    }
}
