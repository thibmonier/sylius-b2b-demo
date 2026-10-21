<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tmonier\SyliusQuoteRequestPlugin\Basket\QuoteBasketInterface;

/** "Quote" scenario: the quote basket of the plugin (the draft of the request form) is replaced by the lines of a purchase list. */
#[AutoconfigureTag(ScenarioPreparer::TAG, ['key' => 'quote'])]
final readonly class QuoteScenarioPreparer implements ScenarioPreparer
{
    public function __construct(
        private ScenarioListResolver $lists,
        private QuoteBasketInterface $basket,
        private ChannelContextInterface $channels,
    ) {
    }

    public function prepare(array $options): void
    {
        $items = $this->lists->items($options);
        $channel = $this->channels->getChannel();
        \assert($channel instanceof ChannelInterface);

        $quantities = [];
        foreach ($items as $item) {
            $variantId = $item->getVariant()?->getId();
            if (\is_int($variantId)) {
                $quantities[$variantId] = max(1, $item->getQuantity());
            }
        }
        if ([] === $quantities) {
            throw new ScenarioPreparationFailed('No line of the list can be quoted.');
        }

        $this->basket->replace($channel, $quantities);
    }
}
