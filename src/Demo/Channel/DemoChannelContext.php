<?php

declare(strict_types=1);

namespace App\Demo\Channel;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The demo serves both channels on one domain (Railway): the channel comes from the `demo_channel`
 * cookie set by the channel switcher, B2B by default (ADR-0011).
 */
final readonly class DemoChannelContext implements ChannelContextInterface
{
    public const COOKIE = 'demo_channel';

    public const B2B = 'B2B';

    public const B2C = 'B2C';

    /** @param ChannelRepositoryInterface<ChannelInterface> $channelRepository */
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private RequestStack $requestStack,
    ) {
    }

    public function getChannel(): ChannelInterface
    {
        $channel = $this->channelRepository->findOneByCode(self::codeFrom($this->requestStack->getMainRequest()?->cookies->get(self::COOKIE)));
        if (!$channel instanceof ChannelInterface) {
            throw new ChannelNotFoundException();
        }

        return $channel;
    }

    public static function codeFrom(mixed $value): string
    {
        return $value === self::B2C ? self::B2C : self::B2B;
    }
}
