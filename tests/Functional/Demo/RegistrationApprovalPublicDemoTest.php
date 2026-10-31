<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * US-243: the demo of the Registration Approval plugin stays usable in public mode. Registrations are closed there
 * (DEMO_REGISTRATION_OPEN=0), so the demo page explains the flow and two fictitious registrations wait for Sophie.
 */
final class RegistrationApprovalPublicDemoTest extends DemoAdminWebTestCase
{
    use EnvironmentSwitch;

    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR', 'Sophie'];
        yield 'en' => ['en_US', 'Sophie'];
    }

    #[DataProvider('locales')]
    public function testThePublicDemoExplainsTheValidationBySophie(string $locale, string $admin): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '0']);
        $client = self::createClient();

        $page = $client->request('GET', '/' . $locale . '/validation-des-inscriptions');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($admin, $page->filter('[data-page="registration-approval"]')->text());
        self::assertCount(1, $page->filter('[data-page="registration-approval"] a[href="/admin/login"]'));
        self::assertCount(1, $page->filter('[data-page="registration-approval"] a[href="/demo/mails"]'));
        self::assertCount(0, $page->filter('a[href$="/register"]'), 'No link to the closed registration form');
    }

    public function testWithRegistrationsOpenTheDemoIsTheRegistrationForm(): void
    {
        $this->setEnvironment(['DEMO_REGISTRATION_OPEN' => '1']);
        $client = self::createClient();

        $client->request('GET', '/fr_FR/validation-des-inscriptions');

        self::assertResponseRedirects('/fr_FR/register');
    }

    public function testTwoFictitiousRegistrationsWaitForSophie(): void
    {
        $client = $this->sophieClient();

        $page = $client->request('GET', '/admin/registration-approvals');

        self::assertResponseIsSuccessful();
        $text = $page->filter('main, body')->first()->text();
        self::assertStringContainsString('nadia.roux@atelier-roux.example', $text);
        self::assertStringContainsString('paul.leroy@maintenance-ouest.example', $text);
    }
}
