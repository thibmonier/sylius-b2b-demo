<?php

declare(strict_types=1);

namespace App\Demo\Analytics;

/**
 * US-117: the `utm_campaign` of a link, cleaned with the rule of the event properties (UmamiEvent::isLabel): a plain
 * lowercase label of 40 characters at most. Anything else (unknown shape, too long, array, empty) is ignored: null, and
 * nothing is ever stored for it.
 */
final class CampaignLabel
{
    public static function clean(mixed $value): ?string
    {
        if (!\is_string($value)) {
            return null;
        }
        $label = strtolower(trim($value));

        return UmamiEvent::isLabel($label) ? $label : null;
    }
}
