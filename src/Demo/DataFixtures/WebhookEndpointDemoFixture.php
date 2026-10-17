<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Webhook\DemoWebhookTarget;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\WebhookEndpointInterface;
use Tmonier\SyliusWebhookOutboxPlugin\EventType\CoreEventTypeProvider;
use Tmonier\SyliusWebhookOutboxPlugin\Signature\SecretGenerator;

/**
 * US-029: the webhook endpoint of the demo, pointing to the receiver of the container (the only URL the demo accepts).
 * The signing secret is generated at each load and read from the database by the receiver: it is never versioned.
 * Loaded after the order history (priority -60) so that the fixtures themselves do not fill the outbox.
 */
final class WebhookEndpointDemoFixture extends AbstractFixture
{
    public const ENDPOINT_NAME = 'Démo — récepteur interne';

    /** @param class-string<WebhookEndpointInterface> $endpointClass */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DemoWebhookTarget $target,
        #[Autowire(service: 'tmonier_webhook.secret_generator')]
        private readonly SecretGenerator $secrets,
        #[Autowire(param: 'tmonier_webhook.model.webhook_endpoint.class')]
        private readonly string $endpointClass,
    ) {
    }

    public function load(array $options): void
    {
        $endpoint = $this->entityManager->getRepository($this->endpointClass)->findOneBy(['name' => self::ENDPOINT_NAME]);
        if ($endpoint instanceof WebhookEndpointInterface) {
            return;
        }

        $endpoint = new $this->endpointClass();
        $endpoint->setName(self::ENDPOINT_NAME);
        $endpoint->setDescription('Récepteur de la démo : les webhooks signés apparaissent dans Intégrations > Webhooks reçus. Aucun appel sortant.');
        $endpoint->setUrl($this->target->url());
        $endpoint->setSecret($this->secrets->generate());
        $endpoint->setEnabled(true);
        $endpoint->setEventTypes([
            CoreEventTypeProvider::ORDER_PLACED,
            CoreEventTypeProvider::ORDER_PAID,
            CoreEventTypeProvider::ORDER_SHIPPED,
            CoreEventTypeProvider::ORDER_CANCELLED,
        ]);
        $endpoint->setChannelCodes([]);

        $this->entityManager->persist($endpoint);
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_webhook_endpoint';
    }
}
