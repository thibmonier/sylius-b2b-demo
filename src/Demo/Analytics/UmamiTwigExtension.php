<?php

declare(strict_types=1);

namespace App\Demo\Analytics;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * US-143: `app_umami_enabled()` is true on a measured page of a configured demo; `app_umami_script_url()` and
 * `app_umami_website_id()` feed the script tag; `app_umami_event('cta-rendez-vous', {emplacement: 'fiche-produit'})`
 * prints the event attributes (a `demo-` prefix is added; a third argument `true` adds the visitor's campaign, US-117). The attributes are inert without the script.
 */
final class UmamiTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly UmamiTracker $tracker,
        private readonly RequestStack $requests,
        private readonly CampaignContext $campaign,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_umami_enabled', $this->isMeasured(...)),
            new TwigFunction('app_umami_script_url', $this->tracker->scriptUrl(...)),
            new TwigFunction('app_umami_website_id', $this->tracker->websiteId(...)),
            new TwigFunction('app_umami_event', $this->event(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, string|int> $properties
     * @param bool                  $withCampaign adds the `campaign` property (US-117: journey launch and contact clicks)
     */
    public function event(string $name, array $properties = [], bool $withCampaign = false): string
    {
        $campaign = $withCampaign ? $this->campaign->current() : null;
        if (null !== $campaign) {
            $properties['campaign'] = $campaign;
        }

        return UmamiEvent::attributes($name, $properties);
    }

    private function isMeasured(): bool
    {
        $request = $this->requests->getMainRequest();

        return null !== $request && $this->tracker->measures($request->getPathInfo());
    }
}
