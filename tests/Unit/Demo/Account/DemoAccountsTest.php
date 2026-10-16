<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Account;

use App\Demo\Account\DemoAccount;
use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Controller\DemoAccountsController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/**
 * US-015: the demo accounts have a single source (config/demo/accounts.yaml) and every
 * page that shows accounts renders only accounts of that source. No database needed.
 */
final class DemoAccountsTest extends KernelTestCase
{
    public function testTheCatalogMatchesTheDocumentedDemoDataset(): void
    {
        $catalog = $this->catalog();
        $members = array_merge(...array_map(static fn ($company): array => $company->members, $catalog->companies()));

        self::assertCount(5, $catalog->companies());
        self::assertCount(11, $members);
        self::assertSame(['thomas@buildco.example'], $this->emails(array_filter($members, static fn (DemoAccount $a): bool => !$a->enabled)));
        self::assertSame(['lea@demo.example'], $this->emails($catalog->consumers()));
        self::assertSame(['sophie@fournitures-pro.example'], $this->emails($catalog->adminUsers()));
        self::assertSame($this->emails($members), array_values(array_unique($this->emails($members))), 'A customer belongs to one company only.');
    }

    public function testAccountsPageListsOnlyCatalogAccountsAndNoFalseMarcMention(): void
    {
        $controller = new DemoAccountsController($this->twig(), $this->catalog(), true);
        $html = $controller()->getContent();
        self::assertIsString($html);

        self::assertEmailsBelongToCatalog($html, $this->allEmails());
        self::assertStringContainsString('marc@techparts.example', $html);
        self::assertStringNotContainsString('aussi dans BuildCo', $html);
        self::assertStringNotContainsString('also in BuildCo', $html);
    }

    public function testShopLoginPageShowsAccountsWithOneClickAndDisabledAccountIsNotClickable(): void
    {
        $html = $this->renderWithSession('demo/login_accounts_shop.html.twig');

        self::assertEmailsBelongToCatalog($html, $this->allEmails());
        self::assertStringContainsString('name="_csrf_shop_security_token"', $html);
        self::assertStringContainsString('/login-check', $html);
        self::assertSame(11, substr_count($html, '<form'), '10 enabled members + Lea; Thomas has no form.');
        self::assertStringContainsString('désactivé', $html);
        self::assertStringNotContainsString('data-demo-login="thomas@buildco.example"', $html);
        self::assertStringContainsString('data-demo-login="claire@acme.example"', $html);
        self::assertStringNotContainsString('sophie@fournitures-pro.example', $html);
    }

    public function testAdminLoginPageShowsAdminAccountsWithOneClick(): void
    {
        $html = $this->renderWithSession('demo/login_accounts_admin.html.twig');

        self::assertStringContainsString('data-demo-login="sophie@fournitures-pro.example"', $html);
        self::assertStringContainsString('name="_csrf_admin_security_token"', $html);
        self::assertStringNotContainsString('claire@acme.example', $html);
    }

    public function testBannerLinksToAccountsAndChannelsAndKeepsTheBadgesSlot(): void
    {
        $html = $this->renderWithSession('demo/banner.html.twig');

        self::assertStringContainsString('/demo/accounts', $html);
        self::assertStringContainsString('/demo/channel/B2B', $html);
        self::assertStringContainsString('/demo/channel/B2C', $html);
        self::assertStringContainsString('data-demo-badges-slot', $html);
        self::assertStringContainsString('Se connecter en tant que Claire', $html);
    }

    public function testTheAccountsPageIsNotFoundWhenTheDemoModeIsOff(): void
    {
        $controller = new DemoAccountsController($this->twig(), $this->catalog(), false);

        $this->expectException(NotFoundHttpException::class);
        $controller();
    }

    public function testNoDemoCredentialIsDuplicatedInFixturesOrControllers(): void
    {
        $root = \dirname(__DIR__, 4);
        foreach (['src/Demo/DataFixtures/CompanyDemoFixture.php', 'src/Demo/DataFixtures/CustomerDemoFixture.php', 'src/Demo/Controller/DemoAccountsController.php'] as $file) {
            self::assertDoesNotMatchRegularExpression('/@[a-z-]+\.example|Demo1234|demo-sophie/', (string) file_get_contents($root . '/' . $file), $file);
        }
        self::assertStringContainsString("'%demo.admin_users%'", (string) file_get_contents($root . '/config/packages/demo_fixtures.yaml'));
    }

    private function catalog(): DemoAccountCatalog
    {
        $catalog = $this->container()->get(DemoAccountCatalog::class);
        self::assertInstanceOf(DemoAccountCatalog::class, $catalog);

        return $catalog;
    }

    private function twig(): Environment
    {
        $twig = $this->container()->get(Environment::class);
        self::assertInstanceOf(Environment::class, $twig);

        return $twig;
    }

    private function container(): \Psr\Container\ContainerInterface
    {
        if (!self::$booted) {
            self::bootKernel();
        }

        return self::getContainer();
    }

    private function renderWithSession(string $template): string
    {
        $container = $this->container();
        $request = Request::create('/fr_FR/login');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack = $container->get('request_stack');
        self::assertInstanceOf(RequestStack::class, $requestStack);
        $requestStack->push($request);

        return $this->twig()->render($template);
    }

    /** @return list<string> */
    private function allEmails(): array
    {
        return $this->emails([...$this->catalog()->shopAccounts(), ...$this->catalog()->adminUsers()]);
    }

    /**
     * @param iterable<DemoAccount> $accounts
     *
     * @return list<string>
     */
    private function emails(iterable $accounts): array
    {
        $emails = [];
        foreach ($accounts as $account) {
            $emails[] = $account->email;
        }

        return $emails;
    }

    /** @param list<string> $catalogEmails */
    private static function assertEmailsBelongToCatalog(string $html, array $catalogEmails): void
    {
        preg_match_all('/[a-z0-9._-]+@[a-z0-9-]+\.example/i', $html, $matches);
        self::assertNotSame([], $matches[0]);
        foreach ($matches[0] as $email) {
            self::assertContains($email, $catalogEmails, \sprintf('"%s" is not in the demo account catalog.', $email));
        }
    }
}
