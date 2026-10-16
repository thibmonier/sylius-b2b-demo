<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The legal notice and the about page exist in the locales of the current channel only; any other locale
 * answers 404 (S4-R5). Runs before the shop's own locale listener, which would redirect to the home page.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 20)]
final readonly class LegalPagesLocaleListener
{
    private const ROUTES = ['app_demo_legal_notice', 'app_demo_about'];

    public function __construct(
        private ChannelContextInterface $channelContext,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !\in_array($request->attributes->get('_route'), self::ROUTES, true)) {
            return;
        }

        $channel = $this->channelContext->getChannel();
        $served = $channel instanceof ChannelInterface ? array_map(
            static fn (LocaleInterface $locale): ?string => $locale->getCode(),
            $channel->getLocales()->toArray(),
        ) : [];
        if (!\in_array($request->attributes->get('_locale'), $served, true)) {
            throw new NotFoundHttpException();
        }
    }
}
