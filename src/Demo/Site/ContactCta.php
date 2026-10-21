<?php

declare(strict_types=1);

namespace App\Demo\Site;

/**
 * US-142: the targets of the "contact me" call to action (LinkedIn profile, contact form), tagged with UTM parameters
 * naming the placement they come from (no cookie). A target that is not configured (or not a plain https URL) is absent.
 */
final readonly class ContactCta
{
    public function __construct(private SiteLinks $links)
    {
    }

    /** @return list<array{kind: 'profile'|'contact', url: string}> */
    public function targets(string $placement): array
    {
        $targets = [];
        foreach (['profile' => $this->links->profileUrl(), 'contact' => $this->links->contactUrl()] as $kind => $url) {
            if (null !== $url) {
                $targets[] = ['kind' => $kind, 'url' => self::tagged($url, $placement)];
            }
        }

        return $targets;
    }

    private static function tagged(string $url, string $placement): string
    {
        $fragment = '';
        if (false !== $hash = strpos($url, '#')) {
            $fragment = substr($url, $hash);
            $url = substr($url, 0, $hash);
        }
        $content = preg_replace('/[^a-z0-9-]/', '', strtolower($placement));
        $utm = 'utm_source=demo-b2b&utm_medium=cta&utm_content=' . $content;

        return $url . (str_contains($url, '?') ? '&' : '?') . $utm . $fragment;
    }
}
