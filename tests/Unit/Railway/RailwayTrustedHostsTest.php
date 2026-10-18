<?php

declare(strict_types=1);

namespace App\Tests\Unit\Railway;

use PHPUnit\Framework\TestCase;

/**
 * US-140: TRUSTED_HOSTS must not depend on RAILWAY_PUBLIC_DOMAIN, which follows the custom domain (incident of 2026-10-18).
 */
final class RailwayTrustedHostsTest extends TestCase
{
    private string $iac;

    protected function setUp(): void
    {
        $this->iac = (string) file_get_contents(\dirname(__DIR__, 3) . '/.railway/railway.ts');
    }

    public function testTrustedHostsIsNotBuiltFromThePublicDomainVariable(): void
    {
        preg_match('/^\s*TRUSTED_HOSTS\b.*$/m', $this->iac, $line);
        self::assertNotEmpty($line, 'The web service must set TRUSTED_HOSTS.');
        self::assertStringNotContainsString('RAILWAY_PUBLIC_DOMAIN', $line[0]);
        self::assertStringNotContainsString('RAILWAY_PUBLIC_DOMAIN', $this->constantLine('TRUSTED_HOSTS'));
    }

    public function testTheServiceDomainIsExplicitAndValidated(): void
    {
        self::assertMatchesRegularExpression('/const RAILWAY_SERVICE_DOMAIN = "[a-z0-9-]+(\.[a-z0-9-]+)+";/', $this->iac);
        self::assertStringContainsString('throw new Error("RAILWAY_SERVICE_DOMAIN must be a bare host name', $this->iac);
    }

    public function testTheExpressionIsAnchoredAndEscapesTheDots(): void
    {
        $line = $this->constantLine('TRUSTED_HOSTS');

        self::assertStringContainsString('`^(${RAILWAY_SERVICE_DOMAIN.replace(/\\./g, "\\\\.")}|healthcheck\\\\.railway\\\\.app)$`', $line);
    }

    private function constantLine(string $name): string
    {
        preg_match('/^const ' . $name . ' = .*$/m', $this->iac, $line);

        return $line[0] ?? '';
    }
}
