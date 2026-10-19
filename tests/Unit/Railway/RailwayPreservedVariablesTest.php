<?php

declare(strict_types=1);

namespace App\Tests\Unit\Railway;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * `railway config apply` deletes the variables of the web service that `.railway/railway.ts` does not declare (plan of
 * 2026-10-19: the Calendly URL set by hand was about to go). Every variable the owner sets in Railway stays declared,
 * with preserve(); and the media volume keeps the zone Railway reports, so that no region change is planned.
 */
final class RailwayPreservedVariablesTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function variablesSetByTheOwner(): iterable
    {
        foreach (['APP_SECRET', 'DEMO_BASIC_AUTH_USER', 'DEMO_BASIC_AUTH_PASSWORD_HASH', 'DEMO_PUBLIC_HOST', 'DEMO_UMAMI_SCRIPT_URL', 'DEMO_UMAMI_WEBSITE_ID', 'DEMO_UMAMI_COLLECT_URL', 'DEMO_BOOKING_URL', 'DEMO_CONTACT_URL', 'DEMO_SITE_URL'] as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('variablesSetByTheOwner')]
    public function testAVariableSetByTheOwnerIsPreserved(string $name): void
    {
        self::assertMatchesRegularExpression('/^\s+' . $name . ': preserve\(\),$/m', self::iac());
    }

    public function testTheMediaVolumeKeepsTheZoneRailwayReports(): void
    {
        self::assertStringContainsString('const MEDIA_VOLUME_ZONE = "europe-west4-drams3a";', self::iac());
        self::assertStringContainsString('volume("media", { sizeMB: 1024, region: MEDIA_VOLUME_ZONE })', self::iac());
    }

    private static function iac(): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 3) . '/.railway/railway.ts');
    }
}
