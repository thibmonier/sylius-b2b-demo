<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PlatformHealth;

use App\Demo\Http\BasicAuthGate;
use App\Demo\Http\DemoConfigurationGuard;
use App\Demo\PlatformHealth\DemoConfigurationCheck;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthCategory;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthStatus;

/** US-321: the fail-closed checks of the demo are a probe of the platform health plugin (they were a branch of HealthController). */
final class DemoConfigurationCheckTest extends TestCase
{
    public function testItIsIdentifiedAndFiledUnderTheEnvironment(): void
    {
        $check = new DemoConfigurationCheck($this->guard(safe: true));

        self::assertSame('demo.configuration', $check->identifier());
        self::assertSame(HealthCategory::Environment, $check->category());
    }

    public function testASafeConfigurationIsOk(): void
    {
        self::assertSame(HealthStatus::Ok, new DemoConfigurationCheck($this->guard(safe: true))->run()->status);
    }

    public function testAnUnsafeConfigurationIsCritical(): void
    {
        self::assertSame(HealthStatus::Critical, new DemoConfigurationCheck($this->guard(safe: false))->run()->status);
    }

    public function testTheSummaryNamesNeitherASecretNorAHost(): void
    {
        $summary = new DemoConfigurationCheck($this->guard(safe: false))->run()->summary;

        self::assertInstanceOf(TranslatableMessage::class, $summary);
        self::assertSame('demo_health.configuration.critical', $summary->getMessage());
        self::assertSame([], $summary->getParameters());
    }

    private function guard(bool $safe): DemoConfigurationGuard
    {
        $mailer = $safe ? 'null://null' : 'smtp://smtp.provider.example';

        return new DemoConfigurationGuard(new BasicAuthGate(false, false, '', ''), true, $mailer, 'mailpit', str_repeat('s', 40), '^demo\.example$');
    }
}
