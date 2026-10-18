<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * US-073: finds the colours written by hand in a stylesheet (hex, rgb(), hsl()). Colours belong in the token file;
 * system colour keywords (CanvasText, ButtonText...) and `currentColor` are not colours of the palette.
 */
final class StyleColorScanner
{
    private const COLOR_LITERAL = '/#[0-9a-fA-F]{3,8}\b|\b(?:rgba?|hsla?)\(\s*\d/';

    /** @return list<string> "line N: <text>" for each line carrying a colour literal */
    public static function hardCoded(string $stylesheet): array
    {
        $found = [];
        foreach (explode("\n", $stylesheet) as $index => $line) {
            $code = trim(preg_replace('~//.*$~', '', $line) ?? '');
            if (1 === preg_match(self::COLOR_LITERAL, $code)) {
                $found[] = sprintf('line %d: %s', $index + 1, $code);
            }
        }

        return $found;
    }
}
