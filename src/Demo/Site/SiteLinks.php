<?php

declare(strict_types=1);

namespace App\Demo\Site;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-074: URLs of the author's site and contact page shown in the shop footer. Configuration only
 * (DEMO_SITE_URL, DEMO_CONTACT_URL, DEMO_BOOKING_URL): an absent or invalid value (anything but a plain https URL) hides the link.
 */
final readonly class SiteLinks
{
    private ?string $siteUrl;

    private ?string $contactUrl;

    private ?string $bookingUrl;

    public function __construct(
        #[Autowire(param: 'demo.site_url')]
        string $siteUrl,
        #[Autowire(param: 'demo.contact_url')]
        string $contactUrl,
        #[Autowire(param: 'demo.booking_url')]
        string $bookingUrl,
    ) {
        $this->siteUrl = self::httpsOrNull($siteUrl);
        $this->contactUrl = self::httpsOrNull($contactUrl);
        $this->bookingUrl = self::httpsOrNull($bookingUrl);
    }

    public function siteUrl(): ?string
    {
        return $this->siteUrl;
    }

    public function contactUrl(): ?string
    {
        return $this->contactUrl;
    }

    public function bookingUrl(): ?string
    {
        return $this->bookingUrl;
    }

    private static function httpsOrNull(string $url): ?string
    {
        $url = trim($url);
        if ('' === $url || 1 === preg_match('/[\x00-\x20\x7f]/', $url)) {
            return null;
        }
        $parts = parse_url($url);
        if (false === $parts || 'https' !== ($parts['scheme'] ?? null) || '' === ($parts['host'] ?? '') || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        return $url;
    }
}
