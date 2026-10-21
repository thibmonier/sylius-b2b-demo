<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Account\DemoAccount;
use App\Demo\Account\DemoAccountCatalog;
use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * US-160: a visitor who is not signed in is told to sign in with the demo account of the journey, without the name nor
 * the role of that account (a one-click button remains, the account itself is already public on the login page).
 */
final class JourneyAnonymousAccountTest extends WebTestCase
{
    use IsolatesSharedAccounts;

    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR', 'Se connecter'];
        yield 'en' => ['en_US', 'Sign in'];
    }

    #[DataProvider('locales')]
    public function testTheAnonymousMessageNamesNeitherTheAccountNorItsRole(string $locale, string $button): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $account = $this->account('claire@acme.example');
        $translator = self::getContainer()->get(TranslatorInterface::class);
        self::assertInstanceOf(TranslatorInterface::class, $translator);
        $role = $translator->trans('role.' . $account->role, [], 'theme_demo', $locale);

        $page = $client->request('GET', '/' . $locale . '/guides/buyer/1');

        self::assertResponseIsSuccessful();
        $box = $page->filter('[data-journey-wrong-account]');
        self::assertCount(1, $box);
        self::assertStringNotContainsString($account->firstName, $box->text());
        self::assertStringNotContainsString($account->lastName, $box->text());
        self::assertStringNotContainsString($role, $box->text());
        self::assertStringContainsString($button, $box->filter('button')->text());
        self::assertCount(1, $box->filter('form button[data-demo-login]'), 'The one-click sign-in remains.');
    }

    public function testTheSignedInVisitorOnAnotherAccountStillSeesWhichAccountIsNeeded(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $needed = $this->account('claire@acme.example');
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => 'paul@acme.example']);
        self::assertInstanceOf(CustomerInterface::class, $customer);
        $user = $customer->getUser();
        self::assertNotNull($user);
        $client->loginUser($user, 'shop');

        $page = $client->request('GET', '/fr_FR/guides/buyer/1');

        self::assertStringContainsString($needed->fullName(), $page->filter('[data-journey-wrong-account]')->text());
    }

    private function account(string $email): DemoAccount
    {
        $catalog = self::getContainer()->get(DemoAccountCatalog::class);
        self::assertInstanceOf(DemoAccountCatalog::class, $catalog);
        $account = $catalog->findShopAccount($email);
        self::assertNotNull($account, $email . ' must exist in the demo accounts.');

        return $account;
    }
}
