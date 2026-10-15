<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Creates the industrial catalogue taxon hierarchy with FR/EN translations.
 *
 * Tree:
 *   catalogue
 *   ├── epi
 *   │   ├── protection-tete
 *   │   ├── protection-mains
 *   │   ├── chaussures-securite
 *   │   └── protection-corps
 *   ├── quincaillerie
 *   │   ├── visserie
 *   │   ├── outillage
 *   │   └── fixations
 *   └── fournitures-industrielles
 *       ├── emballage
 *       ├── signaletique
 *       └── papeterie
 */
final class TaxonDemoFixture extends AbstractFixture
{
    /**
     * @param TaxonFactoryInterface<TaxonInterface>           $taxonFactory
     * @param TaxonRepositoryInterface<TaxonInterface>        $taxonRepository
     */
    public function __construct(
        private readonly TaxonFactoryInterface $taxonFactory,
        #[Autowire(service: 'sylius.repository.taxon')]
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly ObjectManager $objectManager,
    ) {
    }

    public function load(array $options): void
    {
        $tree = $this->buildTree();
        $this->createTree($tree, null);
        $this->objectManager->flush();
    }

    public function getName(): string
    {
        return 'demo_taxons';
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Returns the full taxon tree as nested arrays.
     *
     * Each node: ['code', 'fr' => name, 'en' => name, 'children' => [...]]
     *
     * @return list<array{code: string, fr: string, en: string, children: list<array{code: string, fr: string, en: string, children: list<mixed>}>}>
     */
    private function buildTree(): array
    {
        return [
            [
                'code' => 'catalogue',
                'fr' => 'Catalogue',
                'en' => 'Catalogue',
                'children' => [
                    [
                        'code' => 'epi',
                        'fr' => 'EPI — Équipements de Protection Individuelle',
                        'en' => 'PPE — Personal Protective Equipment',
                        'children' => [
                            ['code' => 'protection-tete', 'fr' => 'Protection de la tête', 'en' => 'Head protection', 'children' => []],
                            ['code' => 'protection-mains', 'fr' => 'Protection des mains', 'en' => 'Hand protection', 'children' => []],
                            ['code' => 'chaussures-securite', 'fr' => 'Chaussures de sécurité', 'en' => 'Safety footwear', 'children' => []],
                            ['code' => 'protection-corps', 'fr' => 'Protection du corps', 'en' => 'Body protection', 'children' => []],
                        ],
                    ],
                    [
                        'code' => 'quincaillerie',
                        'fr' => 'Quincaillerie',
                        'en' => 'Hardware',
                        'children' => [
                            ['code' => 'visserie', 'fr' => 'Visserie & Boulonnerie', 'en' => 'Fasteners & Bolts', 'children' => []],
                            ['code' => 'outillage', 'fr' => 'Outillage', 'en' => 'Tools', 'children' => []],
                            ['code' => 'fixations', 'fr' => 'Fixations & Attaches', 'en' => 'Fixings & Fasteners', 'children' => []],
                        ],
                    ],
                    [
                        'code' => 'fournitures-industrielles',
                        'fr' => 'Fournitures industrielles',
                        'en' => 'Industrial supplies',
                        'children' => [
                            ['code' => 'emballage', 'fr' => 'Emballage & Conditionnement', 'en' => 'Packaging & Wrapping', 'children' => []],
                            ['code' => 'signaletique', 'fr' => 'Signalétique & Sécurité', 'en' => 'Signs & Safety', 'children' => []],
                            ['code' => 'papeterie', 'fr' => 'Papeterie & Bureau', 'en' => 'Office & Stationery', 'children' => []],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param list<array{code: string, fr: string, en: string, children: list<mixed>}> $nodes
     */
    private function createTree(array $nodes, ?TaxonInterface $parent): void
    {
        foreach ($nodes as $node) {
            $taxon = $this->findOrCreateTaxon($node['code'], $parent);
            $this->setTranslation($taxon, 'fr_FR', $node['fr'], $node['code']);
            $this->setTranslation($taxon, 'en_US', $node['en'], $node['code']);
            $this->objectManager->persist($taxon);

            /** @var list<array{code: string, fr: string, en: string, children: list<mixed>}> $children */
            $children = $node['children'];
            $this->createTree($children, $taxon);
        }
    }

    private function findOrCreateTaxon(string $code, ?TaxonInterface $parent): TaxonInterface
    {
        /** @var TaxonInterface|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $code]);

        if ($taxon === null) {
            if ($parent !== null) {
                $taxon = $this->taxonFactory->createForParent($parent);
            } else {
                $taxon = $this->taxonFactory->createNew();
            }
            $taxon->setCode($code);
        }

        return $taxon;
    }

    private function setTranslation(TaxonInterface $taxon, string $locale, string $name, string $slug): void
    {
        $taxon->setCurrentLocale($locale);
        $taxon->setFallbackLocale($locale);
        $taxon->setName($name);
        $taxon->setSlug($slug);
    }
}
