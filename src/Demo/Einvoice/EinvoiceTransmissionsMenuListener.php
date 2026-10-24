<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Translation\TranslatorInterface;

/** US-184: "E-invoice transmissions" in the Integrations group of the admin menu (same group as the received webhooks). */
#[AsEventListener(event: 'sylius.menu.admin.main', priority: -10)]
final readonly class EinvoiceTransmissionsMenuListener
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
            ->addChild('app_demo_einvoice_transmissions', ['route' => 'app_demo_admin_einvoice_transmissions'])
            ->setLabel($this->translator->trans('einvoice.menu', [], 'demo_einvoice'))
            ->setLabelAttribute('icon', 'tabler:file-invoice')
        ;
    }
}
