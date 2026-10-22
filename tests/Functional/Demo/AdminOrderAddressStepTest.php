<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\DomCrawler\Crawler;
use Tmonier\SyliusAdminOrderCreationPlugin\Draft\DraftOrderFactoryInterface;
use Tmonier\SyliusAdminOrderCreationPlugin\Draft\DraftOrderModifierInterface;
use Tmonier\SyliusAdminOrderCreationPlugin\Entity\AdminOrderCreationInterface;
use Tmonier\SyliusAdminOrderCreationPlugin\Remover\DraftRemoverInterface;

/**
 * US-146 (T-146-12): Sophie, the restricted demo account, creates an order for Claire of Acme; the address step offers
 * the shared addresses of Acme (source of company-accounts) in a labelled group, never those of another company.
 * The step is a GET page and the order-creation routes (tmonier_admin_order_) are on the write allow-list of the policy.
 */
final class AdminOrderAddressStepTest extends DemoAdminWebTestCase
{
    private const CLAIRE = 'claire@acme.example';

    private const ACME_STREET = '12 rue de l\'Industrie';

    private const BUILDCO_STREET = '3 quai de la Fosse';

    private ?int $draftId = null;

    protected function tearDown(): void
    {
        if (null !== $this->draftId) {
            $draft = $this->entityManager()->find(AdminOrderCreationInterface::class, $this->draftId);
            $remover = self::getContainer()->get(DraftRemoverInterface::class);
            self::assertInstanceOf(DraftRemoverInterface::class, $remover);
            if ($draft instanceof AdminOrderCreationInterface) {
                $remover->remove($draft);
            }
        }
        $this->draftId = null;

        parent::tearDown();
    }

    /** @return iterable<string, array{string, string}> */
    public static function provideLocales(): iterable
    {
        yield 'French' => ['fr_FR', 'Adresses de l\'entreprise'];
        yield 'English' => ['en_US', 'Company addresses'];
    }

    #[DataProvider('provideLocales')]
    public function testTheAddressStepOffersTheAddressesOfTheCompanyInALabelledGroup(string $locale, string $legend): void
    {
        $client = $this->sophieClient($locale);
        $id = $this->createDraftForClaire();

        $crawler = $client->request('GET', '/admin/order-drafts/' . $id . '/address');

        self::assertResponseIsSuccessful();
        $group = $crawler->filter('form[data-test-address-picker] fieldset[data-test-address-group="company"]');
        self::assertCount(1, $group, 'The company addresses form one fieldset.');
        self::assertSame($legend, trim($group->filter('legend')->text()), 'The fieldset is labelled by its legend.');
        self::assertStringContainsString(self::ACME_STREET, $group->text());
        $this->assertEveryRadioHasALabel($group);
        self::assertGreaterThanOrEqual(3, $group->filter('input[type="radio"]')->count(), 'The three addresses of Acme are offered.');
    }

    public function testTheAddressesOfAnotherCompanyAreNeverOffered(): void
    {
        $client = $this->sophieClient();
        $id = $this->createDraftForClaire();

        $crawler = $client->request('GET', '/admin/order-drafts/' . $id . '/address');

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString(self::BUILDCO_STREET, $this->text($crawler));
        self::assertStringNotContainsString('Nantes', $this->text($crawler));
    }

    public function testAnAddressOfAnotherCompanyIsRefusedAndAnAddressOfAcmeIsCopied(): void
    {
        $client = $this->sophieClient();
        $id = $this->createDraftForClaire();

        $client->request('GET', '/admin/order-drafts/' . $id . '/address?address=company:' . $this->companyAddressId('BUILDCO'));
        self::assertResponseRedirects('/admin/order-drafts/' . $id . '/address');
        $crawler = $client->followRedirect();
        self::assertStringContainsString('Cette adresse n\'est pas disponible pour ce client.', $this->text($crawler));

        $crawler = $client->request('GET', '/admin/order-drafts/' . $id . '/address?address=company:' . $this->companyAddressId('ACME_IND'));
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Lyon', (string) json_encode($crawler->filter('form[data-test-address-form] input')->extract(['value'])));
    }

    private function assertEveryRadioHasALabel(Crawler $group): void
    {
        foreach ($group->filter('input[type="radio"]') as $radio) {
            self::assertInstanceOf(\DOMElement::class, $radio);
            $radioId = $radio->getAttribute('id');
            self::assertNotSame('', $radioId, 'Every radio has an id.');
            self::assertCount(1, $group->filter('label[for="' . $radioId . '"]'), 'Every radio has its label.');
        }
    }

    private function companyAddressId(string $companyCode): int
    {
        return $this->intValue(
            'SELECT ca.id FROM tmonier_company_address ca JOIN tmonier_company c ON c.id = ca.company_id WHERE c.code = ? ORDER BY ca.id LIMIT 1',
            [$companyCode],
        );
    }

    private function createDraftForClaire(): int
    {
        $container = self::getContainer();
        $draftFactory = $container->get(DraftOrderFactoryInterface::class);
        $modifier = $container->get(DraftOrderModifierInterface::class);
        self::assertInstanceOf(DraftOrderFactoryInterface::class, $draftFactory);
        self::assertInstanceOf(DraftOrderModifierInterface::class, $modifier);

        $channel = $this->repository('sylius.repository.channel')->findOneBy(['code' => 'B2B']);
        $customer = $this->repository('sylius.repository.customer')->findOneBy(['emailCanonical' => self::CLAIRE]);
        $variant = $this->repository('sylius.repository.product_variant')->findOneBy(['code' => 'VISS-001']);
        self::assertInstanceOf(ChannelInterface::class, $channel);
        self::assertInstanceOf(CustomerInterface::class, $customer);
        self::assertInstanceOf(ProductVariantInterface::class, $variant);

        $admin = $this->sophie();
        self::assertInstanceOf(AdminUserInterface::class, $admin);
        $draft = $draftFactory->create($channel, $customer, null, $admin);
        $order = $draft->getOrder();
        self::assertNotNull($order);
        $modifier->addVariant($order, $variant, 2);
        $this->entityManager()->flush();

        $this->draftId = (int) $draft->getId();

        return (int) $order->getId();
    }

    private function entityManager(): EntityManagerInterface
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $manager);

        return $manager;
    }

    /** @return RepositoryInterface<ResourceInterface> */
    private function repository(string $id): RepositoryInterface
    {
        $repository = self::getContainer()->get($id);
        self::assertInstanceOf(RepositoryInterface::class, $repository);

        return $repository;
    }
}
