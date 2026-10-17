<?php

declare(strict_types=1);

namespace App\Demo\Webhook;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Translation\TranslatorInterface;

/** US-029: "Received webhooks" in the Integrations group of the admin menu (the webhook plugin creates the group, this runs after it). */
#[AsEventListener(event: 'sylius.menu.admin.main', priority: -10)]
final readonly class ReceivedWebhooksMenuListener
{
    private const GROUP = 'integrations';

    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function __invoke(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $group = $menu->getChild(self::GROUP) ?? $menu
            ->addChild(self::GROUP)
            ->setLabel($this->translator->trans('tmonier_webhook.ui.integrations'))
            ->setLabelAttribute('icon', 'tabler:plug-connected')
        ;

        $group
            ->addChild('app_demo_received_webhooks', ['route' => 'app_demo_admin_received_webhooks', 'extras' => ['routes' => [
                ['route' => 'app_demo_admin_received_webhook'],
            ]]])
            ->setLabel($this->translator->trans('menu', [], 'demo_webhooks'))
            ->setLabelAttribute('icon', 'tabler:inbox')
        ;
    }
}
