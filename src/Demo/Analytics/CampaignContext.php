<?php

declare(strict_types=1);

namespace App\Demo\Analytics;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * US-117: the campaign (`utm_campaign`) a visitor arrived with, to tag the launch of a journey and the contact clicks.
 * It comes from the current URL, or from the session once the visitor arrived on the guided journeys (the only place where it is stored, so
 * that a visitor who never opens a journey never gets a session for it). An invalid value is ignored and stores nothing.
 */
final readonly class CampaignContext
{
    private const PARAMETER = 'utm_campaign';

    private const SESSION_KEY = 'demo_campaign';

    public function __construct(private RequestStack $requests)
    {
    }

    public function current(): ?string
    {
        $request = $this->requests->getMainRequest();
        if (null === $request) {
            return null;
        }
        $fromUrl = CampaignLabel::clean($request->query->get(self::PARAMETER));
        if (null !== $fromUrl || !$request->hasPreviousSession()) {
            return $fromUrl;
        }

        return CampaignLabel::clean($request->getSession()->get(self::SESSION_KEY));
    }

    /** Keeps the valid campaign of the URL for the next pages of the journey. */
    public function remember(Request $request): void
    {
        $campaign = CampaignLabel::clean($request->query->get(self::PARAMETER));
        if (null !== $campaign) {
            $request->getSession()->set(self::SESSION_KEY, $campaign);
        }
    }
}
