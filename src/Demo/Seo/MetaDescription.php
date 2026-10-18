<?php

declare(strict_types=1);

namespace App\Demo\Seo;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * US-078: the meta description of a page. A page that has its own text (product, category) is described by it;
 * the others by a sentence per route in the `seo` translation domain, with a default for the routes left out.
 */
final readonly class MetaDescription
{
    public const MAX_LENGTH = 160;

    private const DOMAIN = 'seo';

    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function resolve(string $route, ?string $ownText, string $name = ''): string
    {
        $excerpt = $this->excerpt($ownText ?? '');
        if ('' !== $excerpt) {
            return $excerpt;
        }

        $id = 'description.' . str_replace('.', '_', $route);
        $parameters = ['%name%' => $name];
        $description = $this->translator->trans($id, $parameters, self::DOMAIN);

        return '' === $route || $description === $id ? $this->translator->trans('description.default', $parameters, self::DOMAIN) : $description;
    }

    /** Text without markup or entities, to be escaped once by the template (never printed raw). */
    public function plain(string $html): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), \ENT_QUOTES | \ENT_HTML5)));
    }

    private function excerpt(string $html): string
    {
        $text = $this->plain($html);
        if (mb_strlen($text) <= self::MAX_LENGTH) {
            return $text;
        }

        $cut = mb_substr($text, 0, self::MAX_LENGTH - 1);
        $lastSpace = mb_strrpos($cut, ' ');

        return rtrim(false === $lastSpace ? $cut : mb_substr($cut, 0, $lastSpace), ' ,;:.-') . '…';
    }
}
