<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Admin\DemoAdminRole;
use App\Demo\DataFixtures\DemoAdminRoleFixture;
use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-292 (T-292-04): the platform health page of the plugin in the demo. The demo sets `access.role` to
 * ROLE_PLATFORM_HEALTH_VIEW and the demo_admin_role fixture grants it to the public back-office accounts, at every load.
 * Run against a database loaded with the `demo` suite; the writes of a test are rolled back.
 */
final class PlatformHealthDemoTest extends DemoAdminWebTestCase
{
    use IsolatesSharedAccounts;

    private const URL = '/admin/platform-health';

    private const ROLE = 'ROLE_PLATFORM_HEALTH_VIEW';

    /** The probes of the plugin (v0.4) by category, all measured on the demo, none of them written by the demo. */
    private const PROBES = [
        'environment' => [
            'environment.php_version',
            'environment.symfony_version',
            'environment.sylius_version',
            'environment.php_extensions',
            'environment.opcache',
            'environment.app_debug',
            'environment.cache_pool',
            'environment.writable_dirs',
        ],
        'data' => [
            'data.connection',
            'data.pending_migrations',
            'data.schema_drift',
            'data.table_growth',
            'data.messenger_pending',
            'data.messenger_oldest',
            'data.messenger_failed',
        ],
        'scheduled_tasks' => ['scheduled_tasks.heartbeat'],
    ];

    public function testEveryPublicAdminAccountOfTheCatalogueCarriesTheRoleAfterTheFixturesLoad(): void
    {
        self::bootKernel();
        $catalog = self::getContainer()->get(DemoAccountCatalog::class);
        self::assertInstanceOf(DemoAccountCatalog::class, $catalog);
        self::assertNotSame([], $catalog->adminUsers());

        foreach ($catalog->adminUsers() as $account) {
            self::assertContains(self::ROLE, $this->adminUser($account->email)->getRoles(), $account->email . ' must keep the role after a fixtures load.');
        }
    }

    public function testTheFixtureGrantsTheRoleAgainAfterItWasRemovedAndNeverTwice(): void
    {
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $sophie = $this->adminUser(self::SOPHIE);
        $sophie->removeRole(self::ROLE);
        $this->entityManager()->flush();
        self::assertNotContains(self::ROLE, $this->adminUser(self::SOPHIE)->getRoles());

        $fixture = self::getContainer()->get(DemoAdminRoleFixture::class);
        self::assertInstanceOf(DemoAdminRoleFixture::class, $fixture);
        $fixture->load([]);
        $fixture->load([]);

        $roles = $this->adminUser(self::SOPHIE)->getRoles();
        self::assertContains(self::ROLE, $roles);
        self::assertContains(DemoAdminRole::NAME, $roles);
        self::assertCount(\count(array_unique($roles)), $roles, 'The roles of the account must stay a set.');
    }

    public function testSophieSeesThePageAndItsSixteenProbesOnTheRealImage(): void
    {
        $client = $this->sophieClient();
        $page = $client->request('GET', self::URL);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Santé de la plateforme', $this->text($page));

        $results = $this->results($client, $page);

        foreach (self::PROBES as $category => $probes) {
            $section = $results->filter('[data-test-health-category="' . $category . '"]');
            self::assertCount(1, $section, $category);
            $identifiers = $section->filter('[data-test-health-check]')->each(static fn (Crawler $row): string => (string) $row->attr('data-test-health-check'));
            self::assertEqualsCanonicalizing($probes, $identifiers, $category);
        }
        self::assertCount(16, $results->filter('[data-test-health-check]'));
    }

    public function testThePluginMigrationCreatedTheHeartbeatTable(): void
    {
        self::bootKernel();
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        self::assertInstanceOf(Connection::class, $connection);

        self::assertTrue($connection->createSchemaManager()->tablesExist(['tmonier_platform_health_heartbeat']));
    }

    public function testEveryWatchedTableIsATableOfTheDemo(): void
    {
        self::bootKernel();
        $tables = array_map(
            static fn (ClassMetadata $metadata): string => $metadata->getTableName(),
            $this->entityManager()->getMetadataFactory()->getAllMetadata(),
        );

        foreach (['tmonier_audit_log_entry', 'tmonier_webhook_delivery', 'tmonier_punchout_message_log'] as $watched) {
            self::assertContains($watched, $tables);
        }
    }

    public function testTheMenuEntryIsShownToAnAccountWithTheRole(): void
    {
        $page = $this->sophieClient()->request('GET', '/admin/');

        self::assertGreaterThan(0, $page->filter('a[href$="' . self::URL . '"]')->count());
    }

    public function testAnAdministratorWithoutTheRoleGets403OnThePageOnTheComponentAndOnTheRefresh(): void
    {
        $client = $this->sophieClient();
        $page = $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();
        $rendered = $this->postToComponent($client, $page);
        self::assertResponseIsSuccessful();

        $this->isolateSharedAccounts($client);
        $this->adminUser(self::SOPHIE)->removeRole(self::ROLE);
        $this->entityManager()->flush();
        $client->loginUser($this->adminUser(self::SOPHIE), 'admin');

        $client->request('GET', self::URL);
        self::assertResponseStatusCodeSame(403);

        $this->postToComponent($client, $page);
        self::assertResponseStatusCodeSame(403);

        $this->postToComponent($client, $rendered, 'refresh');
        self::assertResponseStatusCodeSame(403);
    }

    public function testThePageAndTheRefreshAreNotRefusedByTheDemoWriteGuard(): void
    {
        $client = $this->sophieClient();
        $page = $client->request('GET', self::URL);
        self::assertResponseIsSuccessful();

        // The deferred render and the refresh are POSTs under the administration prefix: the guard must let them through.
        $rendered = $this->postToComponent($client, $page);
        self::assertResponseIsSuccessful('The deferred render of the component must not be refused by the guard of the demo.');

        $this->postToComponent($client, $rendered, 'refresh');
        self::assertResponseIsSuccessful('The refresh of the component is read-only: the guard of the demo must let it through.');
        self::assertStringContainsString('data-test-health-category="environment"', (string) $client->getResponse()->getContent());
    }

    /**
     * The deferred render and the actions of a live component are POSTs on its URL, as the browser sends them. The CSRF
     * token of the actions is carried by the rendered component, not by its deferred placeholder.
     */
    private function postToComponent(KernelBrowser $client, Crawler $from, string $action = ''): Crawler
    {
        $root = $from->filter('[data-controller~="live"]');
        self::assertGreaterThan(0, $root->count(), 'The page must carry the live component.');
        $url = (string) strtok($this->attribute($root, 'data-live-url-value'), '?');
        $props = json_decode($this->attribute($root, 'data-live-props-value'), true, 512, \JSON_THROW_ON_ERROR);

        return $client->request('POST', '' === $action ? $url : $url . '/' . $action, [
            'data' => json_encode(['props' => $props, 'updated' => [], 'args' => []], \JSON_THROW_ON_ERROR),
        ], [], [
            'HTTP_ACCEPT' => 'application/vnd.live-component+html',
            'HTTP_X_CSRF_TOKEN' => $root->attr('data-live-csrf-value') ?? '',
        ]);
    }

    private function results(KernelBrowser $client, Crawler $page): Crawler
    {
        $crawler = $this->postToComponent($client, $page);
        self::assertResponseIsSuccessful();

        return $crawler;
    }

    private function attribute(Crawler $node, string $name): string
    {
        $value = $node->attr($name);
        self::assertIsString($value, $name . ' is missing on the live component.');

        return $value;
    }

    private function adminUser(string $email): AdminUserInterface
    {
        $repository = self::getContainer()->get('sylius.repository.admin_user');
        self::assertInstanceOf(RepositoryInterface::class, $repository);
        $user = $repository->findOneBy(['emailCanonical' => mb_strtolower($email)]);
        self::assertInstanceOf(AdminUserInterface::class, $user);

        return $user;
    }

    private function entityManager(): EntityManagerInterface
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $manager);

        return $manager;
    }
}
