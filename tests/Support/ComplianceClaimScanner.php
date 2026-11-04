<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * US-235: finds the sentences of a text that present the digital product passport as compliant or certified.
 *
 * Rule: a sentence is a claim when it mentions the passport (passeport / passport / DPP) AND
 *  - contains an adjective of assertion (conforme, certifié, compliant, certified), or
 *  - contains the noun conformité / compliance without a negation (sans, aucune, pas, no, not, non, without).
 * The disclaimer « sans promesse de conformité réglementaire » therefore passes: the noun is negated. A sentence that
 * does not mention the passport is out of scope (the rest of the demo may speak of e-invoicing compliance).
 */
final class ComplianceClaimScanner
{
    private const SUBJECT = '/passeport|passport|\bDPP\b/iu';

    private const ASSERTION = '/\b(conformes?|certifi[ée]e?s?|compliant|certified)\b|conformité garantie|guaranteed compliance|compliance guaranteed/iu';

    private const NOUN = '/conformité|compliance/iu';

    private const NEGATION = '/\b(sans|aucune?|pas|non|no|not|without)\b/iu';

    /** @return list<string> */
    public static function claims(string $text): array
    {
        $text = html_entity_decode(strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/is', ' ', $text) ?? $text), \ENT_QUOTES | \ENT_HTML5);
        $claims = [];
        foreach (preg_split('/(?<=[.!?])\s+|\R+/u', $text) ?: [] as $sentence) {
            if (1 === preg_match(self::SUBJECT, $sentence) && self::isClaim($sentence)) {
                $claims[] = trim($sentence);
            }
        }

        return $claims;
    }

    private static function isClaim(string $sentence): bool
    {
        if (1 === preg_match(self::ASSERTION, $sentence)) {
            return true;
        }

        return 1 === preg_match(self::NOUN, $sentence) && 1 !== preg_match(self::NEGATION, $sentence);
    }
}
