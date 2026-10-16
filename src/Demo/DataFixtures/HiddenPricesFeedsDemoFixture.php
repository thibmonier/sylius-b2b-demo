<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\DBAL\Connection;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * US-019 — The product feeds carry the prices. A feed without access token is served to anybody, so the feeds
 * of the B2B channel (prices hidden from visitors who are not signed in) get a random access token, generated
 * at each load: no secret in the repository. Runs after the agentic feeds fixture.
 */
final class HiddenPricesFeedsDemoFixture extends AbstractFixture
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function load(array $options): void
    {
        $feedIds = $this->connection->fetchFirstColumn(
            'SELECT f.id FROM tmonier_agentic_feed f JOIN sylius_channel c ON c.id = f.channel_id WHERE c.code = :code AND f.access_token IS NULL',
            ['code' => 'B2B'],
        );

        foreach ($feedIds as $feedId) {
            $this->connection->update('tmonier_agentic_feed', ['access_token' => bin2hex(random_bytes(24))], ['id' => $feedId]);
        }
    }

    public function getName(): string
    {
        return 'demo_hidden_prices_feeds';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        // No options needed.
    }
}
