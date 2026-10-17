<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * US-050 / T-050-02: the findings of the security review made before the public opening. Anonymous product reviews
 * (a guest customer without the registration), the shared customer accounts of the demo (password and e-mail), the
 * preflight on Railway without the demo mode, and the global rate limit of the shop. Asserted on the served answers
 * and on the database.
 */
final class PublicOpeningHardeningTest extends WebTestCase
{
    use DatabaseAccess;
    use EnvironmentSwitch;

    private const CLAIRE = 'claire@acme.example';

    public function testAnonymousProductReviewCannotCreateAGuestCustomerOnceRegistrationsAreClosed(): void
    {
        $this->publicMode();
        $client = $this->consumerClient();
        $url = $this->reviewUrl();
        $customers = $this->intValue('SELECT COUNT(*) FROM sylius_customer');

        $crawler = $client->request('GET', $url);
        self::assertResponseStatusCodeSame(403);
        self::assertStringContainsString('Les inscriptions sont fermées', (string) $client->getResponse()->getContent());
        self::assertCount(0, $crawler->filter('input[type="email"], input[name*="email"]'));

        $client->request('POST', $url, ['sylius_shop_product_review' => [
            'title' => 'Super', 'rating' => '5', 'comment' => 'Bien', 'author' => ['email' => 'avis-' . bin2hex(random_bytes(3)) . '@client.example', 'firstName' => 'A', 'lastName' => 'B'],
        ]]);

        self::assertResponseStatusCodeSame(403);
        self::assertSame($customers, $this->intValue('SELECT COUNT(*) FROM sylius_customer'));
    }

    public function testTheGuestReviewFormIsServedWhileRegistrationsAreOpen(): void
    {
        $this->publicMode();
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '1', 'DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '0']);
        $client = $this->consumerClient();

        $crawler = $client->request('GET', $this->reviewUrl());

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('input[name*="[email]"]')->count(), 'the guest asks for an e-mail');
    }

    public function testAProductReviewIsRateLimitedPerVisitor(): void
    {
        $this->publicMode();
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '1', 'DEMO_RATE_LIMIT_FORMS' => '2']);
        $client = $this->consumerClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        for ($attempt = 0; $attempt < 2; ++$attempt) {
            $client->request('POST', $this->reviewUrl(), [], [], $visitor);
            self::assertNotSame(429, $client->getResponse()->getStatusCode());
        }
        $client->request('POST', $this->reviewUrl(), [], [], $visitor);

        self::assertResponseStatusCodeSame(429);
    }

    public function testASharedDemoCustomerCannotChangeThePassword(): void
    {
        $this->publicMode();
        $client = self::createClient();
        $this->login($client, self::CLAIRE);
        $hash = $this->stringValue('SELECT u.password FROM sylius_shop_user u JOIN sylius_customer c ON c.id = u.customer_id WHERE c.email = ?', [self::CLAIRE]);

        $client->followRedirects();
        $client->request('POST', '/fr_FR/account/change-password', ['sylius_user_change_password' => [
            'currentPassword' => 'Demo1234!x', 'newPassword' => ['first' => 'Hacked1234!x', 'second' => 'Hacked1234!x'],
        ]]);

        self::assertStringContainsString('compte de démo partagé', mb_strtolower((string) $client->getResponse()->getContent()));
        self::assertSame($hash, $this->stringValue('SELECT u.password FROM sylius_shop_user u JOIN sylius_customer c ON c.id = u.customer_id WHERE c.email = ?', [self::CLAIRE]));
    }

    public function testASharedDemoCustomerCannotChangeTheEmailButTheProfilePageIsServed(): void
    {
        $this->publicMode();
        $client = self::createClient();
        $this->login($client, self::CLAIRE);

        $client->request('GET', '/fr_FR/account/profile/edit');
        self::assertResponseIsSuccessful();

        $client->followRedirects();
        $client->request('PUT', '/fr_FR/account/profile/edit', ['sylius_shop_customer_profile' => [
            'firstName' => 'Claire', 'lastName' => 'Acme', 'email' => 'pirate@client.example',
        ]]);

        self::assertStringContainsString('compte de démo partagé', mb_strtolower((string) $client->getResponse()->getContent()));
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM sylius_customer WHERE email = ?', ['pirate@client.example']));
        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM sylius_customer WHERE email = ?', [self::CLAIRE]));
    }

    public function testThePreflightFailsOnRailwayWithoutTheDemoMode(): void
    {
        $this->setEnvironment(['DEMO_MODE' => '0', 'RAILWAY_ENVIRONMENT' => 'production']);

        [$status, $output] = $this->preflight();

        self::assertSame(1, $status);
        self::assertStringContainsString('DEMO_MODE', $output);
    }

    public function testThePreflightIgnoresTheDemoModeOffOutsideRailway(): void
    {
        $this->setEnvironment(['DEMO_MODE' => '0', 'RAILWAY_ENVIRONMENT' => '']);

        [$status] = $this->preflight();

        self::assertSame(0, $status);
    }

    public function testTheShopIsRateLimitedPerVisitorButNotTheHealthchecks(): void
    {
        $this->publicMode();
        $this->setEnvironment(['DEMO_RATE_LIMIT_SHOP' => '3']);
        $client = self::createClient();
        $visitor = ['HTTP_X_FORWARDED_FOR' => self::visitorIp()];

        for ($request = 0; $request < 3; ++$request) {
            $client->request('GET', '/fr_FR/', server: $visitor);
            self::assertResponseIsSuccessful();
        }
        $client->request('GET', '/fr_FR/', server: $visitor);
        self::assertResponseStatusCodeSame(429);
        self::assertTrue($client->getResponse()->headers->has('Retry-After'));

        $client->request('GET', '/healthz', server: $visitor);
        self::assertNotSame(429, $client->getResponse()->getStatusCode());

        $client->request('GET', '/fr_FR/', server: ['HTTP_X_FORWARDED_FOR' => self::visitorIp()]);
        self::assertResponseIsSuccessful();
    }

    private function publicMode(): void
    {
        $this->setEnvironment(['DEMO_BASIC_AUTH' => '0', 'DEMO_REGISTRATION_OPEN' => '0', 'DEMO_PUNCHOUT_PUBLIC_ENDPOINT' => '0']);
    }

    private function consumerClient(): KernelBrowser
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));

        return $client;
    }

    private function reviewUrl(): string
    {
        $slug = $this->stringValue('SELECT t.slug FROM sylius_product_translation t JOIN sylius_product p ON p.id = t.translatable_id WHERE t.locale = ? AND p.enabled = 1 ORDER BY p.id LIMIT 1', ['fr_FR']);

        return '/fr_FR/products/' . $slug . '/reviews/new';
    }

    /** @return array{int, string} */
    private function preflight(): array
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('app:demo:preflight'));

        return [$tester->execute([]), $tester->getDisplay()];
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
