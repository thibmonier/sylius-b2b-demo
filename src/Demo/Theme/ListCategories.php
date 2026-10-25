<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Doctrine\DBAL\Connection;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

/**
 * The categories of the side of the product list (US-198) with the number of products each one holds in the current channel: the leaves of
 * the taxonomy that have products, in the order of the tree, in ONE query for the request.
 */
final class ListCategories
{
    /** @var list<array{name: string, slug: string, count: int}>|null */
    private ?array $categories = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly ChannelContextInterface $channelContext,
        private readonly LocaleContextInterface $localeContext,
    ) {
    }

    /** @return list<array{name: string, slug: string, count: int}> */
    public function all(): array
    {
        return $this->categories ??= $this->load();
    }

    /** @return list<array{name: string, slug: string, count: int}> */
    private function load(): array
    {
        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return [];
        }
        if (!$channel instanceof ChannelInterface) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT tt.name AS name, tt.slug AS slug, COUNT(DISTINCT p.id) AS total
             FROM sylius_taxon t
             JOIN sylius_taxon_translation tt ON tt.translatable_id = t.id AND tt.locale = :locale
             JOIN sylius_product_taxon pt ON pt.taxon_id = t.id
             JOIN sylius_product p ON p.id = pt.product_id AND p.enabled = 1
             JOIN sylius_product_channels pc ON pc.product_id = p.id AND pc.channel_id = :channel
             WHERE t.enabled = 1 AND t.tree_right = t.tree_left + 1
             GROUP BY t.id, tt.name, tt.slug, t.tree_left
             ORDER BY t.tree_left',
            ['locale' => $this->localeContext->getLocaleCode(), 'channel' => $channel->getId()],
        );

        return array_map(static fn (array $row): array => [
            'name' => \is_string($row['name']) ? $row['name'] : '',
            'slug' => \is_string($row['slug']) ? $row['slug'] : '',
            'count' => is_numeric($row['total']) ? (int) $row['total'] : 0,
        ], $rows);
    }
}
