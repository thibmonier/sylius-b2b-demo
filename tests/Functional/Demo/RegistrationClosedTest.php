<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

/**
 * US-040 / M2: when the registrations are closed (DEMO_REGISTRATION_OPEN=0, set before the public opening), a visitor
 * cannot enter personal data that every visitor could then read in Sophie's customer list. Asserted on the rendered
 * pages and on the database.
 */
final class RegistrationClosedTest extends DemoAdminWebTestCase
{
    use EnvironmentSwitch;

    private const LAST_NAME = 'Visiteuse-M2';

    public function testTheRegistrationFormIsStillServedByDefault(): void
    {
        $client = self::createClient();

        $client->request('GET', '/fr_FR/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="sylius_shop_customer_registration"]');
    }

    public function testAClosedRegistrationShowsAMessageInsteadOfTheForm(): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '0']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/register');

        self::assertResponseStatusCodeSame(403);
        self::assertSelectorNotExists('form[name="sylius_shop_customer_registration"]');
        self::assertStringContainsString('Les inscriptions sont fermées', (string) $client->getResponse()->getContent());
    }

    public function testAVisitorWhoPostsARegistrationCreatesNothingAndSophieDoesNotSeeTheData(): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '0']);
        $client = self::createClient();
        $email = 'm2-' . bin2hex(random_bytes(4)) . '@client.example';
        $customers = $this->intValue('SELECT COUNT(*) FROM sylius_customer');

        $client->request('POST', '/fr_FR/register', ['sylius_shop_customer_registration' => [
            'firstName' => 'Alice',
            'lastName' => self::LAST_NAME,
            'email' => $email,
            'user' => ['plainPassword' => ['first' => 'Demo1234!x', 'second' => 'Demo1234!x']],
        ]]);

        self::assertResponseStatusCodeSame(403);
        self::assertSame($customers, $this->intValue('SELECT COUNT(*) FROM sylius_customer'));

        $client->loginUser($this->sophie(), 'admin');
        $client->request('GET', '/admin/customers/?sorting[createdAt]=desc');
        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString(self::LAST_NAME, (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString($email, (string) $client->getResponse()->getContent());
    }

    public function testTheShopApiCannotCreateACustomerEither(): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '0']);
        $client = self::createClient();
        $email = 'm2-api-' . bin2hex(random_bytes(4)) . '@client.example';

        $client->request('POST', '/api/v2/shop/customers', server: ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'], content: (string) json_encode([
            'email' => $email,
            'firstName' => 'Alice',
            'lastName' => self::LAST_NAME,
            'password' => 'Demo1234!x',
        ]));

        self::assertResponseStatusCodeSame(403);
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM sylius_customer WHERE email = ?', [$email]));
    }
}
