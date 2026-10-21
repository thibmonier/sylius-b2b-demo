<?php

declare(strict_types=1);

namespace App\Demo\Analytics;

/**
 * US-143: builds the Umami `data-umami-event` attributes. Event names are prefixed `demo-` (the demo shares the Umami
 * site of the author's website); names and property values are plain lowercase labels, so an e-mail address, a company
 * name or an order number can never be sent by mistake: anything else is dropped.
 */
final class UmamiEvent
{
    private const PREFIX = 'demo-';

    private const LABEL = '/^[a-z0-9][a-z0-9_-]{0,39}$/';

    /** A plain lowercase label (letters, digits, `_`, `-`, 40 characters at most): the only shape a property value may take. */
    public static function isLabel(string $value): bool
    {
        return 1 === preg_match(self::LABEL, $value);
    }

    /** @param array<string, string|int> $properties */
    public static function attributes(string $name, array $properties = []): string
    {
        $name = str_starts_with($name, self::PREFIX) ? $name : self::PREFIX . $name;
        if (1 !== preg_match('/^demo-[a-z0-9][a-z0-9_-]{0,44}$/', $name)) {
            return '';
        }

        $attributes = ' data-umami-event="' . $name . '"';
        foreach ($properties as $key => $value) {
            $value = (string) $value;
            if (self::isLabel((string) $key) && self::isLabel($value)) {
                $attributes .= ' data-umami-event-' . $key . '="' . $value . '"';
            }
        }

        return $attributes;
    }
}
