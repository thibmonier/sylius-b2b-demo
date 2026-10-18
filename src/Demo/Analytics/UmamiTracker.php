<?php

declare(strict_types=1);

namespace App\Demo\Analytics;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-143: Umami audience measurement. Configuration only (DEMO_UMAMI_SCRIPT_URL, DEMO_UMAMI_WEBSITE_ID): an absent or
 * invalid value (script not in https, site id not a UUID) disables the measure silently, so no script is rendered and the
 * CSP stays as it is. Only the allow-listed public pages are measured.
 */
final readonly class UmamiTracker
{
    /**
     * Allow-list of the measured pages: the public shop pages and the PunchOut simulator. Everything else (back-office,
     * accounts, login, password and e-mail verification, orders, quotes, any URL with a private token) is never measured.
     */
    private const MEASURED_PAGES = '#^(/[a-z]{2}_[A-Z]{2}(/(products|taxons|plugins|mcp|a-propos|mentions-legales|quote-request)(/[A-Za-z0-9_./-]*)?)?/?|/demo/punchout-simulator(/[a-z/]*)?)$#';

    private ?string $scriptUrl;

    private ?string $websiteId;

    private ?string $origin;

    public function __construct(
        #[Autowire(param: 'demo.umami_script_url')]
        string $scriptUrl,
        #[Autowire(param: 'demo.umami_website_id')]
        string $websiteId,
    ) {
        $origin = self::httpsOrigin(trim($scriptUrl));
        $id = trim($websiteId);
        $valid = null !== $origin && 1 === preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id);

        $this->scriptUrl = $valid ? trim($scriptUrl) : null;
        $this->websiteId = $valid ? $id : null;
        $this->origin = $valid ? $origin : null;
    }

    public function isEnabled(): bool
    {
        return null !== $this->scriptUrl;
    }

    public function scriptUrl(): ?string
    {
        return $this->scriptUrl;
    }

    public function websiteId(): ?string
    {
        return $this->websiteId;
    }

    /** The script host, also the collection endpoint (CSP script-src and connect-src). */
    public function origin(): ?string
    {
        return $this->origin;
    }

    public function measures(string $path): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return 1 === preg_match(self::MEASURED_PAGES, $path);
    }

    private static function httpsOrigin(string $url): ?string
    {
        if ('' === $url || 1 === preg_match('/[\x00-\x20\x7f"\'<>\\\\]/', $url)) {
            return null;
        }
        $parts = parse_url($url);
        if (false === $parts || 'https' !== ($parts['scheme'] ?? null) || '' === ($parts['host'] ?? '') || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        return 'https://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }
}
