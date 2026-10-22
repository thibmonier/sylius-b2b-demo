<?php

declare(strict_types=1);

namespace App\Demo\Charter;

/**
 * US-113 / US-108: the charter of the public texts of the demo, which proves a skill and does not sell a service.
 * Three checks: a wording that reads as a vendor demo, a figure that nobody can check in a public source, and a
 * commercial wording. Pure functions on a text, shared by the tests of every page that carries the charter.
 */
final class PublicTextCharter
{
    /** Figures a public text may state, each with the public place where anyone can verify it. */
    public const VERIFIABLE_FIGURES = [
        '25' => 'years of delivery: the LinkedIn profile of the author (DEMO_PROFILE_URL)',
    ];

    /** @var list<string> */
    private const AMBIGUOUS = [
        '/d[ée]mos?\s+(?:b2b\s+)?(?:officielle\s+)?de\s+sylius/iu',
        '/sylius(?:\'s)?\s+(?:b2b\s+)?(?:official\s+)?demo/iu',
        '/d[ée]mo\s+officielle/iu',
        '/official\s+(?:b2b\s+)?demo/iu',
        '/(?:par|by)\s+sylius\b/iu',
        '/d[ée]mo\s+(?:de\s+l\'?[ée]diteur|d\'[ée]diteur)/iu',
        '/(?:vendor|publisher)\s+demo/iu',
    ];

    /** @var list<string> */
    private const COMMERCIAL = [
        '/\bsur[\s-]mesure\b/iu',
        '/\bdevis\b/iu',
        '/nous\s+d[ée]veloppons\s+pour\s+vous/iu',
        '/\btailor[\s-]made\b/iu',
        '/\bbespoke\b/iu',
        '/\bwe\s+(?:build|develop)\s+(?:it\s+)?for\s+you\b/iu',
        '/\b(?:get|request)\s+a\s+quote\b/iu',
    ];

    /** @return list<string> the passages that read as a vendor demo */
    public function ambiguities(string $text): array
    {
        return $this->matches(self::AMBIGUOUS, $text);
    }

    /** @return list<string> the commercial wordings found */
    public function commercialWording(string $text): array
    {
        return $this->matches(self::COMMERCIAL, $text);
    }

    /**
     * @param list<string> $alsoAllowed figures computed by the page itself (the plugin count, read from the catalogue)
     *
     * @return list<string> the figures of the text that are neither whitelisted nor allowed by the caller
     */
    public function unsourcedFigures(string $text, array $alsoAllowed = []): array
    {
        $text = (string) preg_replace('/%[A-Za-z_]+%/', '', $text);
        preg_match_all('/(?<![\p{L}\d])\d+(?![\p{L}\d])/u', $text, $found);
        $allowed = [...array_map('strval', array_keys(self::VERIFIABLE_FIGURES)), ...$alsoAllowed];

        return array_values(array_filter($found[0], static fn (string $figure): bool => !\in_array($figure, $allowed, true)));
    }

    /**
     * @param list<string> $patterns
     *
     * @return list<string>
     */
    private function matches(array $patterns, string $text): array
    {
        $hits = [];
        foreach ($patterns as $pattern) {
            if (1 === preg_match($pattern, $text, $match)) {
                $hits[] = $match[0];
            }
        }

        return $hits;
    }
}
