<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Admin\DemoAdminRole;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Base of the US-035 tests: real HTTP requests as Sophie against a database loaded with the `demo` suite.
 */
abstract class DemoAdminWebTestCase extends WebTestCase
{
    use DatabaseAccess;

    protected const SOPHIE = 'sophie@fournitures-pro.example';

    protected function sophieClient(string $locale = 'fr_FR'): KernelBrowser
    {
        $client = self::createClient();
        $this->db()->executeStatement('UPDATE sylius_admin_user SET locale_code = ? WHERE email_canonical = ?', [$locale, self::SOPHIE]);
        $client->loginUser($this->sophie(), 'admin');

        return $client;
    }

    protected function sophie(): AdminUserInterface
    {
        $repository = self::getContainer()->get('sylius.repository.admin_user');
        self::assertInstanceOf(RepositoryInterface::class, $repository);
        $user = $repository->findOneBy(['emailCanonical' => self::SOPHIE]);
        self::assertInstanceOf(AdminUserInterface::class, $user);
        self::assertContains(DemoAdminRole::NAME, $user->getRoles());

        return $user;
    }

    /** Checksum of the rows of the tables a refused write could have touched. */
    protected function snapshot(string ...$tables): string
    {
        $parts = [];
        foreach ($tables as $table) {
            $parts[$table] = $this->db()->fetchAllAssociative(\sprintf('SELECT * FROM %s ORDER BY 1', $table));
        }

        return md5(serialize($parts));
    }

    /**
     * The values of the edit form of an admin page (hidden _method and CSRF token included), as a browser would
     * post them, so that a refusal can only come from the guard and not from an invalid payload.
     *
     * @return array{uri: string, method: string, values: array<string, mixed>}
     */
    protected function editForm(KernelBrowser $client, string $url): array
    {
        $crawler = $client->request('GET', $url);
        self::assertResponseIsSuccessful(\sprintf('The edit page %s must open for the restricted account (read stays possible).', $url));

        $forms = $crawler->filterXPath('//form[.//input[@name="_method"][@value="PUT"] or .//input[contains(@name, "[_token]")]]');
        self::assertGreaterThan(0, $forms->count(), 'No edit form found on ' . $url);
        $form = $forms->first()->form();

        /** @var array<string, mixed> $values */
        $values = $form->getPhpValues();

        return ['uri' => $form->getUri(), 'method' => $form->getMethod(), 'values' => $values];
    }

    /**
     * @param array<string, mixed> $values
     */
    protected function setValue(array &$values, string $path, mixed $value): void
    {
        // explode() always yields the first segment: a string key (PHP 8.5 deprecates null as an array offset).
        $segments = explode('.', $path, 2);
        $key = $segments[0];
        $rest = $segments[1] ?? null;
        if (null === $rest) {
            $values[$key] = $value;

            return;
        }

        $child = $values[$key] ?? [];
        self::assertIsArray($child);
        $this->setValue($child, $rest, $value);
        $values[$key] = $child;
    }

    protected function assertDemoRefusal(KernelBrowser $client, string $expectedMessage): void
    {
        $response = $client->getResponse();
        if ($response->isRedirection()) {
            $page = $client->followRedirect();
            self::assertStringContainsString($expectedMessage, $this->text($page));

            return;
        }

        self::assertSame(403, $response->getStatusCode());
        self::assertStringContainsString($expectedMessage, (string) $response->getContent());
    }

    protected function text(Crawler $crawler): string
    {
        return html_entity_decode($crawler->text(null, true));
    }
}
