<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Translation\TranslatorInterface;

/** US-188: "ERP exchanges" in the Integrations group of the admin menu (same group as the received webhooks and the e-invoice transmissions). */
#[AsEventListener(event: 'sylius.menu.admin.main', priority: -20)]
final readonly class ErpExchangesMenuListener
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
            ->addChild('app_demo_erp_exchanges', ['route' => 'app_demo_admin_erp_exchanges'])
            ->setLabel($this->translator->trans('erp.menu', [], 'demo_erp'))
            ->setLabelAttribute('icon', 'tabler:arrows-exchange')
        ;
    }
}
