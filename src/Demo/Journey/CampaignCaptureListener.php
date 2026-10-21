<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use App\Demo\Analytics\CampaignContext;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/** US-117: keeps the campaign of a link to the guided journeys (and only there) for the pages of the journey. */
#[AsEventListener(event: RequestEvent::class)]
final readonly class CampaignCaptureListener
{
    private const JOURNEY_PAGES = '#^/[a-z]{2}_[A-Z]{2}/guides(/|$)#';

    public function __construct(private CampaignContext $campaign)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($event->isMainRequest() && $request->isMethod('GET') && 1 === preg_match(self::JOURNEY_PAGES, $request->getPathInfo())) {
            $this->campaign->remember($request);
        }
    }
}
