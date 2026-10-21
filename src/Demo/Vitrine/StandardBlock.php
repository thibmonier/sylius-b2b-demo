<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

/**
 * The standard "B2B demo" block of the plugin READMEs (US-064) and the cross-language link.
 * Links: the demo repository, the online demo and the author's profile (US-068). No link to a website or a contact page,
 * no offer and no availability wording. The profile URL is an input (never stored in this repository).
 */
final class StandardBlock
{
    public const START = '<!-- vitrine:demo-b2b:start -->';

    public const END = '<!-- vitrine:demo-b2b:end -->';

    public const LANG_MARKER = '<!-- vitrine:lang -->';

    public const DEMO_URL = 'https://github.com/thibmonier/sylius-b2b-demo';

    public const LIVE_URL = 'https://sylius-b2b.thibaut-monier.fr';

    private const LIVE_LABEL = 'sylius-b2b.thibaut-monier.fr';

    private const AUTHOR = 'Thibaut Monier';

    /** Plugins the demo does not include (US-054, design stage). */
    private const NOT_IN_DEMO = ['sylius-customer-asset-management-plugin'];

    public static function isInDemo(string $plugin): bool
    {
        return !\in_array($plugin, self::NOT_IN_DEMO, true);
    }

    /** @throws \RuntimeException when the URL is not an https address usable in a Markdown link */
    public static function assertProfileUrl(string $url): void
    {
        $host = \parse_url($url, \PHP_URL_HOST);
        if (!\str_starts_with($url, 'https://') || !\is_string($host) || '' === $host || 1 === \preg_match('/[\s()<>\[\]]/', $url)) {
            throw new \RuntimeException('The profile URL must be an https address without spaces or brackets (--profile-url=https://...).');
        }
    }

    /** The profile URL carried by an already treated README, null when there is none. */
    public static function profileUrlOf(string $content): ?string
    {
        return 1 === \preg_match('/^- (?:Author|Auteur) ?: \[' . self::AUTHOR . '\]\((https:[^)\s]+)\)$/m', $content, $match) ? $match[1] : null;
    }

    public static function render(string $language, bool $inDemo, string $profileUrl): string
    {
        self::assertProfileUrl($profileUrl);
        $body = 'fr' === $language ? self::french($inDemo, $profileUrl) : self::english($inDemo, $profileUrl);

        return self::START . "\n" . $body . "\n" . self::END;
    }

    public static function languageLink(string $language): string
    {
        return ('fr' === $language ? '> English: [README.md](README.md) ' : '> Français : [README.fr.md](README.fr.md) ') . self::LANG_MARKER;
    }

    private static function english(bool $inDemo, string $profileUrl): string
    {
        $demo = '[Sylius B2B demo](' . self::DEMO_URL . ')';
        if (!$inDemo) {
            return "## B2B demo\n\nStatus: **not present in the demo** yet. The {$demo} does not include this plugin, and no demo page or data exists for it.\n\n- Author: [" . self::AUTHOR . "]({$profileUrl})";
        }

        return "## B2B demo\n\nThis plugin is used in the {$demo}: a Sylius 2.3 shop with fictitious sample data where the B2B plugins work together. The source code, setup and fixtures of the demo are in that repository.\n\n- Online demo: [" . self::LIVE_LABEL . "](" . self::LIVE_URL . ")\n- Author: [" . self::AUTHOR . "]({$profileUrl})\n\nThe demo is an example, not a production setup.";
    }

    private static function french(bool $inDemo, string $profileUrl): string
    {
        $demo = '[démo B2B Sylius](' . self::DEMO_URL . ')';
        if (!$inDemo) {
            return "## Démo B2B\n\nStatut : **non présent dans la démo** pour l'instant. La {$demo} n'intègre pas ce plugin, et aucune page ni donnée de démo n'existe pour lui.\n\n- Auteur : [" . self::AUTHOR . "]({$profileUrl})";
        }

        return "## Démo B2B\n\nCe plugin est utilisé dans la {$demo} : une boutique Sylius 2.3 avec des données d'exemple fictives, où les plugins B2B fonctionnent ensemble. Le code source, l'installation et les fixtures de la démo sont dans ce dépôt.\n\n- Démo en ligne : [" . self::LIVE_LABEL . "](" . self::LIVE_URL . ")\n- Auteur : [" . self::AUTHOR . "]({$profileUrl})\n\nLa démo est un exemple, pas une configuration de production.";
    }
}
