<?php

declare(strict_types=1);

namespace App\Demo\Site;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** The validated site and contact URLs (null = hidden) for the footer and the demo bar (US-074, US-076). */
final class SiteLinksTwigExtension extends AbstractExtension
{
    public function __construct(private readonly SiteLinks $links)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_demo_site_url', $this->links->siteUrl(...)),
            new TwigFunction('app_demo_contact_url', $this->links->contactUrl(...)),
        ];
    }
}
