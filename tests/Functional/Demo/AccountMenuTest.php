<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\DataSets;
use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-160: the account menu of the header has an accessible name, in both languages (pa11y WCAG2AAA flagged it at sprint 17). */
final class AccountMenuTest extends WebTestCase
{
    use IsolatesSharedAccounts;

    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR', 'Mon compte'];
        yield 'en' => ['en_US', 'My account'];
    }

    #[DataProvider('locales')]
    public function testTheAccountMenuButtonHasAnAccessibleName(string $locale, string $name): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, 'claire@acme.example');

        $page = $client->request('GET', '/' . $locale . '/');

        self::assertResponseIsSuccessful();
        $toggle = $page->filter('header button[data-account-menu-toggle]');
        self::assertCount(1, $toggle);
        self::assertSame($name, $toggle->attr('aria-label'));
    }

    #[DataProvider('localesWith1Argument')]
    public function testEveryButtonOfTheHeaderIsNamed(string $locale): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, 'claire@acme.example');

        $page = $client->request('GET', '/' . $locale . '/');

        $unnamed = $page->filter('header button')->reduce(
            static fn ($button): bool => '' === trim($button->text('')) && '' === trim((string) $button->attr('aria-label')) && '' === trim((string) $button->attr('aria-labelledby')),
        );
        self::assertCount(0, $unnamed, 'A button without text, aria-label or aria-labelledby has no accessible name.');
    }

    private function login(KernelBrowser $client, string $email): void
    {
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer);
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }

    /** @return iterable<array-key, list<mixed>> */
    public static function localesWith1Argument(): iterable
    {
        return DataSets::firstArguments(self::locales(), 1);
    }
}
