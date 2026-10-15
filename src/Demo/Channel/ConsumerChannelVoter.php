<?php

declare(strict_types=1);

namespace App\Demo\Channel;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * `is_granted('DEMO_CONSUMER_CHANNEL')` in twig hook conditions: consumer-only features (withdrawal)
 * are shown on the B2C channel only (ADR-0009).
 *
 * @extends Voter<string, mixed>
 */
final class ConsumerChannelVoter extends Voter
{
    public const ATTRIBUTE = 'DEMO_CONSUMER_CHANNEL';

    public function __construct(private readonly ChannelContextInterface $channelContext)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ATTRIBUTE;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->channelContext->getChannel()->getCode() === DemoChannelContext::B2C;
    }
}
