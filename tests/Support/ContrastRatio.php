<?php

declare(strict_types=1);

namespace App\Tests\Support;

/** WCAG 2.x contrast ratio between two #rrggbb (or #rgb) colours. */
final class ContrastRatio
{
    public static function between(string $foreground, string $background): float
    {
        $lighter = max(self::luminance($foreground), self::luminance($background));
        $darker = min(self::luminance($foreground), self::luminance($background));

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private static function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (3 === \strlen($hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        [$red, $green, $blue] = array_map(
            static fn (string $channel): float => self::linear(hexdec($channel) / 255),
            str_split($hex, 2),
        );

        return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
    }

    private static function linear(float $value): float
    {
        return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
    }
}
