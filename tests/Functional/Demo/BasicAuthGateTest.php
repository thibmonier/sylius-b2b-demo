<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Http\InternalRequestToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-032 / T-032-01, T-032-04: the private gate, seen from the outside (real requests through the kernel).
 * Run against a database loaded with the `demo` fixture suite.
 */
final class BasicAuthGateTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const USER = 'visitor';

    private const PASSWORD = 'correct horse battery';

    public function testWithoutCredentialsTheShopAnswers401WithAChallenge(): void
    {
        $this->privateDemo();
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');

        self::assertResponseStatusCodeSame(401);
        self::assertStringStartsWith('Basic realm=', (string) $client->getResponse()->headers->get('WWW-Authenticate'));
        self::assertStringNotContainsString('Fournitures Pro', (string) $client->getResponse()->getContent());
    }

    public function testWithValidCredentialsTheShopIsServed(): void
    {
        $this->privateDemo();
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: $this->credentials(self::USER, self::PASSWORD));

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Fournitures Pro', (string) $client->getResponse()->getContent());
    }

    #[DataProvider('provideWrongCredentials')]
    public function testWrongCredentialsAreRefused(string $user, string $password): void
    {
        $this->privateDemo();
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: $this->credentials($user, $password));

        self::assertResponseStatusCodeSame(401);
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideWrongCredentials(): iterable
    {
        yield 'wrong password' => [self::USER, 'nope'];
        yield 'wrong user' => ['intruder', self::PASSWORD];
        yield 'empty password' => [self::USER, ''];
    }

    public function testTheAdminAndTheApiAreBehindTheGateToo(): void
    {
        $this->privateDemo();
        $client = self::createClient();

        foreach (['/admin/login', '/api/v2/shop/products', '/demo/accounts', '/punchout/cxml'] as $path) {
            $client->request('GET', $path);
            self::assertResponseStatusCodeSame(401, $path);
        }
    }

    #[DataProvider('provideHealthPaths')]
    public function testTheHealthcheckIsOutsideTheGate(string $path): void
    {
        $this->privateDemo();
        $client = self::createClient();

        $client->request('GET', $path);

        self::assertResponseStatusCodeSame(200);
        self::assertSame('ok', $client->getResponse()->getContent());
    }

    /** @return iterable<string, array{string}> */
    public static function provideHealthPaths(): iterable
    {
        yield '/health' => ['/health'];
        yield '/healthz' => ['/healthz'];
    }

    public function testTheGateCanBeSwitchedOff(): void
    {
        $this->privateDemo();
        $this->setEnvironment(['DEMO_BASIC_AUTH' => '0']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
    }

    public function testTheSimulatorTokenGetsThroughTheGate(): void
    {
        $this->privateDemo();
        $client = self::createClient();
        $token = self::getContainer()->get(InternalRequestToken::class);
        self::assertInstanceOf(InternalRequestToken::class, $token);

        $client->request('GET', '/fr_FR/', server: ['HTTP_X_DEMO_INTERNAL' => $token->value()]);
        self::assertResponseIsSuccessful();

        $client->request('GET', '/fr_FR/', server: ['HTTP_X_DEMO_INTERNAL' => 'forged']);
        self::assertResponseStatusCodeSame(401);
    }

    public function testWithoutCredentialsInTheEnvironmentNothingIsServedAndTheHealthIsRed(): void
    {
        $this->setEnvironment(['DEMO_BASIC_AUTH' => '1', 'DEMO_BASIC_AUTH_USER' => '', 'DEMO_BASIC_AUTH_PASSWORD_HASH' => '']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: $this->credentials('', ''));
        self::assertResponseStatusCodeSame(503);

        $client->request('GET', '/health');
        self::assertResponseStatusCodeSame(503);
        self::assertSame('misconfigured', $client->getResponse()->getContent());
    }

    public function testAClearPasswordInPlaceOfAHashIsRefusedAtStartup(): void
    {
        $this->setEnvironment(['DEMO_BASIC_AUTH' => '1', 'DEMO_BASIC_AUTH_USER' => self::USER, 'DEMO_BASIC_AUTH_PASSWORD_HASH' => self::PASSWORD]);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/', server: $this->credentials(self::USER, self::PASSWORD));

        self::assertResponseStatusCodeSame(503);
    }

    public function testRepeatedWrongPasswordsAreThrottled(): void
    {
        $this->privateDemo();
        $this->setEnvironment(['DEMO_RATE_LIMIT_GATE' => '3']);
        $client = self::createClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $client->request('GET', '/fr_FR/', server: $this->credentials(self::USER, 'guess' . $attempt) + $visitor);
            self::assertResponseStatusCodeSame(401);
        }
        $client->request('GET', '/fr_FR/', server: $this->credentials(self::USER, self::PASSWORD) + $visitor);
        self::assertResponseStatusCodeSame(429, 'Even the right password waits once the visitor is throttled.');

        $client->request('GET', '/fr_FR/', server: $this->credentials(self::USER, self::PASSWORD) + ['HTTP_X_FORWARDED_FOR' => self::visitorIp()]);
        self::assertResponseIsSuccessful();
    }

    private function privateDemo(): void
    {
        $this->setEnvironment([
            'DEMO_BASIC_AUTH' => '1',
            'DEMO_BASIC_AUTH_USER' => self::USER,
            'DEMO_BASIC_AUTH_PASSWORD_HASH' => self::gateHash(self::PASSWORD),
        ]);
    }

    /** @return array<string, string> */
    private function credentials(string $user, string $password): array
    {
        return ['PHP_AUTH_USER' => $user, 'PHP_AUTH_PW' => $password];
    }
}
