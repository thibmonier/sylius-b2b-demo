<?php

declare(strict_types=1);

namespace App\Demo\Badge;

use App\Demo\Plugin\DemoPlugin;
use App\Demo\Plugin\DemoPluginCatalog;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Exposes the badge preference and the plugin lookup to the badge templates (US-016/US-017). */
final class BadgeTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly BadgePreference $preference,
        private readonly DemoPluginCatalog $catalog,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('demo_badges_visible', $this->preference->visible(...)),
            // An unknown slug yields null: the template then renders nothing, without any error.
            new TwigFunction('demo_plugin_count', $this->catalog->count(...)),
            new TwigFunction('demo_plugin', fn (mixed $slug): ?DemoPlugin => \is_string($slug) ? $this->catalog->get($slug) : null),
        ];
    }
}
