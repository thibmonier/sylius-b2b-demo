<?php

declare(strict_types=1);

namespace App\Demo\Render;

/**
 * T-TECH-31-04: the thumbnails (LiipImagine) of a page rendered by bin/render-page, found in its HTML whatever the form of the
 * URL (absolute on the local host or relative, with or without "resolve/") and linked as /media/cache/<reference>, so that the
 * page can be served next to public/media.
 */
final class RenderedThumbnails
{
    /** A relative URL follows a quote, a space, a comma (srcset), an equals sign or a parenthesis: a URL of another host does not match. */
    private const URL = '#(?:http://127\.0\.0\.1|(?<=["\'\s,(=]))/media/cache/(?:resolve/)?';

    /** @return list<string> the references (filter and file, "b2b_photo/x.jpg"), once each, in the order of the page */
    public static function references(string $html): array
    {
        preg_match_all(self::URL . '([^"\'\s,)]+)#', $html, $matches);

        return array_values(array_unique($matches[1]));
    }

    public static function relink(string $html, string $reference): string
    {
        return (string) preg_replace(self::URL . preg_quote($reference, '#') . '#', '/media/cache/' . $reference, $html);
    }
}
