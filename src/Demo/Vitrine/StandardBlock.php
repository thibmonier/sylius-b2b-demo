<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

/**
 * The standard "B2B demo" block of the plugin READMEs (US-064) and the cross-language link.
 * No link to a website or a contact page (decision D1): while the demo is private, it points to its public repository.
 */
final class StandardBlock
{
    public const START = '<!-- vitrine:demo-b2b:start -->';

    public const END = '<!-- vitrine:demo-b2b:end -->';

    public const LANG_MARKER = '<!-- vitrine:lang -->';

    public const DEMO_URL = 'https://github.com/thibmonier/sylius-b2b-demo';

    /** Plugins the demo does not include (US-054, design stage). */
    private const NOT_IN_DEMO = ['sylius-customer-asset-management-plugin'];

    public static function isInDemo(string $plugin): bool
    {
        return !\in_array($plugin, self::NOT_IN_DEMO, true);
    }

    public static function render(string $language, bool $inDemo): string
    {
        $body = 'fr' === $language ? self::french($inDemo) : self::english($inDemo);

        return self::START . "\n" . $body . "\n" . self::END;
    }

    public static function languageLink(string $language): string
    {
        return ('fr' === $language ? '> English: [README.md](README.md) ' : '> Français : [README.fr.md](README.fr.md) ') . self::LANG_MARKER;
    }

    private static function english(bool $inDemo): string
    {
        $demo = '[Sylius B2B demo](' . self::DEMO_URL . ')';
        if (!$inDemo) {
            return "## B2B demo\n\nStatus: **not present in the demo** yet. The {$demo} does not include this plugin, and no demo page or data exists for it.";
        }

        return "## B2B demo\n\nThis plugin is used in the {$demo}: a Sylius 2.3 shop with fictitious sample data where the B2B plugins work together. The source code, setup and fixtures of the demo are in that repository.\n\nThe demo is an example, not a production setup.";
    }

    private static function french(bool $inDemo): string
    {
        $demo = '[démo B2B Sylius](' . self::DEMO_URL . ')';
        if (!$inDemo) {
            return "## Démo B2B\n\nStatut : **non présent dans la démo** pour l'instant. La {$demo} n'intègre pas ce plugin, et aucune page ni donnée de démo n'existe pour lui.";
        }

        return "## Démo B2B\n\nCe plugin est utilisé dans la {$demo} : une boutique Sylius 2.3 avec des données d'exemple fictives, où les plugins B2B fonctionnent ensemble. Le code source, l'installation et les fixtures de la démo sont dans ce dépôt.\n\nLa démo est un exemple, pas une configuration de production.";
    }
}
