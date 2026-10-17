<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\PunchOutSimulator\OciReturnGate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;

/** US-053: an OCI return is used once. */
final class OciReturnGateTest extends TestCase
{
    public function testAReturnIsClaimedOnceThenRefused(): void
    {
        $gate = new OciReturnGate(new ArrayAdapter(), new LockFactory(new InMemoryStore()));

        self::assertTrue($gate->claim('nonce-1'));
        self::assertFalse($gate->claim('nonce-1'));
    }

    public function testAnotherReturnIsNotAffected(): void
    {
        $gate = new OciReturnGate(new ArrayAdapter(), new LockFactory(new InMemoryStore()));
        $gate->claim('nonce-1');

        self::assertTrue($gate->claim('nonce-2'));
    }
}
