<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Http;

use App\Demo\Http\BasicAuthGate;
use App\Demo\Http\DemoConfigurationGuard;
use App\Demo\Http\HostAllowList;
use App\Demo\Http\VisitorIpResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/** US-032 / US-036: the pure decisions behind the private gate, the fail-closed checks and the visitor key. */
final class DemoGuardsTest extends TestCase
{
    public function testTheVisitorIsTheClientAddressNeverAPlaceholderOfTheProxy(): void
    {
        $request = Request::create('/', server: ['REMOTE_ADDR' => '198.51.100.20']);

        self::assertSame('198.51.100.20', (new VisitorIpResolver())->resolve($request));
    }

    public function testTheForwardedAddressIsOnlyReadFromATrustedProxy(): void
    {
        Request::setTrustedProxies(['10.0.0.1'], Request::HEADER_X_FORWARDED_FOR);

        try {
            $trusted = Request::create('/', server: ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_FOR' => '203.0.113.5']);
            $untrusted = Request::create('/', server: ['REMOTE_ADDR' => '192.0.2.99', 'HTTP_X_FORWARDED_FOR' => '203.0.113.5']);

            self::assertSame('203.0.113.5', (new VisitorIpResolver())->resolve($trusted));
            self::assertSame('192.0.2.99', (new VisitorIpResolver())->resolve($untrusted));
        } finally {
            Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);
        }
    }

    public function testTheGateIsOffOutsideTheDemoModeAndWhenSwitchedOff(): void
    {
        self::assertFalse($this->gate(demoMode: false)->isActive());
        self::assertFalse($this->gate(enabled: false)->isActive());
        self::assertTrue($this->gate()->isActive());
    }

    #[DataProvider('provideBrokenGates')]
    public function testAnActiveGateWithoutUsableCredentialsIsMisconfigured(string $user, string $hash): void
    {
        self::assertTrue($this->gate(user: $user, hash: $hash)->isMisconfigured());
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideBrokenGates(): iterable
    {
        yield 'no user' => ['', password_hash('x', \PASSWORD_DEFAULT)];
        yield 'no hash' => ['visitor', ''];
        yield 'clear password instead of a hash' => ['visitor', 'secret'];
    }

    public function testAnInactiveGateIsNeverMisconfigured(): void
    {
        self::assertFalse($this->gate(enabled: false, user: '', hash: '')->isMisconfigured());
    }

    public function testOnlyTheRightUserAndPasswordAreAuthorized(): void
    {
        $gate = $this->gate();

        self::assertTrue($gate->authorizes($this->basic('visitor', 'secret')));
        self::assertFalse($gate->authorizes($this->basic('visitor', 'other')));
        self::assertFalse($gate->authorizes($this->basic('other', 'secret')));
        self::assertFalse($gate->authorizes(Request::create('/')));
    }

    #[DataProvider('provideMailers')]
    public function testTheMailerMustBeInternalInTheDemoMode(string $dsn, bool $accepted): void
    {
        $guard = $this->guard($this->gate(enabled: false), $dsn, 'mailpit,localhost');

        self::assertSame($accepted, [] === $guard->violations());
    }

    /** @return iterable<string, array{string, bool}> */
    public static function provideMailers(): iterable
    {
        yield 'compose mailpit' => ['smtp://mailpit:1025', true];
        yield 'loopback' => ['smtp://127.0.0.1:1025', true];
        yield 'railway private network' => ['smtp://mailpit.railway.internal:1025', true];
        yield 'null' => ['null://null', true];
        yield 'external smtp' => ['smtp://smtp.gmail.com:587', false];
        yield 'look-alike host' => ['smtp://mailpit.evil.example:25', false];
        yield 'sendmail' => ['sendmail://default', false];
        yield 'native' => ['native://default', false];
        yield 'failover to a real one' => ['failover(smtp://mailpit:1025 smtp://smtp.gmail.com)', false];
        yield 'unparsable' => ['%%%', false];
    }

    public function testNothingIsCheckedOutsideTheDemoMode(): void
    {
        $guard = new DemoConfigurationGuard($this->gate(demoMode: false, user: '', hash: ''), false, 'smtp://smtp.gmail.com', '', '', '');

        self::assertSame([], $guard->violations());
    }

    public function testTheViolationsNameNeitherASecretNorAHost(): void
    {
        $guard = $this->guard($this->gate(user: '', hash: ''), 'smtp://user:pass@smtp.provider.example:587', '');

        $messages = implode(' ', $guard->violations());
        self::assertNotSame('', $messages);
        self::assertStringNotContainsString('provider', $messages);
        self::assertStringNotContainsString('pass', $messages);
    }

    #[DataProvider('provideWeakSettings')]
    public function testAWeakSecretOrAnOpenHostWhitelistIsRefusedInTheDemoMode(string $secret, string $hosts, string $mention): void
    {
        $guard = new DemoConfigurationGuard($this->gate(enabled: false), true, 'null://null', '', $secret, $hosts);

        self::assertStringContainsString($mention, implode(' ', $guard->violations()));
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function provideWeakSettings(): iterable
    {
        yield 'short secret' => ['short', '^demo\.example$', 'APP_SECRET'];
        yield 'empty secret' => ['', '^demo\.example$', 'APP_SECRET'];
        yield 'default hosts' => [str_repeat('s', 40), '^.*$', 'TRUSTED_HOSTS'];
        yield 'empty hosts' => [str_repeat('s', 40), '', 'TRUSTED_HOSTS'];
    }

    public function testASolidSecretAndAHostWhitelistPass(): void
    {
        $guard = new DemoConfigurationGuard($this->gate(enabled: false), true, 'null://null', '', str_repeat('s', 32), '^demo\.example$');

        self::assertSame([], $guard->violations());
    }

    public function testTheHostAllowListAcceptsLoopbackRailwayInternalAndConfiguredHostsOnly(): void
    {
        $list = HostAllowList::fromCsv(' Mailpit , api.iconify.design ');

        foreach (['mailpit', 'api.iconify.design', 'localhost', '127.0.0.1', 'web.railway.internal'] as $host) {
            self::assertTrue($list->allows($host), $host);
        }
        foreach (['example.com', 'mailpit.example.com', 'railway.internal.evil.example', '169.254.169.254'] as $host) {
            self::assertFalse($list->allows($host), $host);
        }
    }

    private function guard(BasicAuthGate $gate, string $dsn, string $mailerHosts): DemoConfigurationGuard
    {
        return new DemoConfigurationGuard($gate, true, $dsn, $mailerHosts, str_repeat('s', 40), '^demo\.example$');
    }

    private function gate(bool $demoMode = true, bool $enabled = true, string $user = 'visitor', ?string $hash = null): BasicAuthGate
    {
        return new BasicAuthGate($demoMode, $enabled, $user, $hash ?? password_hash('secret', \PASSWORD_DEFAULT));
    }

    private function basic(string $user, string $password): Request
    {
        return Request::create('/', server: ['PHP_AUTH_USER' => $user, 'PHP_AUTH_PW' => $password]);
    }
}
