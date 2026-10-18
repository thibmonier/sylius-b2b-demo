<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Seo;

use App\Demo\Seo\MetaDescription;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

/** US-078: every page carries a meta description, written for its route or taken from its own content. */
final class MetaDescriptionTest extends TestCase
{
    public function testAPageWithItsOwnTextUsesItWithoutMarkup(): void
    {
        self::assertSame('Vis inox A2, sac de 100.', $this->resolver()->resolve('sylius_shop_product_show', "<p>Vis  inox\nA2, <strong>sac</strong> de 100.</p>", 'Vis'));
    }

    public function testALongTextIsCutAtAWordBoundaryUnderTheLimit(): void
    {
        $text = str_repeat('Quincaillerie professionnelle ', 12);

        $description = $this->resolver()->resolve('sylius_shop_product_show', $text, 'Vis');

        self::assertLessThanOrEqual(MetaDescription::MAX_LENGTH, mb_strlen($description));
        self::assertStringEndsWith('…', $description);
        self::assertStringNotContainsString('Quincaillerie professionnelle Quinc…', $description);
    }

    public function testAnEmptyOrBlankTextFallsBackToTheRouteDescriptionWithTheName(): void
    {
        self::assertSame('Catalogue Visserie pour les professionnels.', $this->resolver()->resolve('sylius_shop_product_index', "  \n ", 'Visserie'));
        self::assertSame('Catalogue Visserie pour les professionnels.', $this->resolver()->resolve('sylius_shop_product_index', null, 'Visserie'));
    }

    public function testARouteWithoutADescriptionGetsTheDefaultOne(): void
    {
        self::assertSame('Démonstration B2B.', $this->resolver()->resolve('sylius_shop_cart_summary', null));
    }

    public function testADottedRouteNameIsReadAsUnderscores(): void
    {
        self::assertSame('Accueil de la démo.', $this->resolver()->resolve('sylius_shop.homepage', null));
    }

    public function testNoRouteGetsTheDefaultOne(): void
    {
        self::assertSame('Démonstration B2B.', $this->resolver()->resolve('', null));
    }

    public function testPlainTextHasNoMarkupNorEntitiesSoItIsEscapedOnlyOnce(): void
    {
        self::assertSame('Vis "A2" & écrou', $this->resolver()->plain(" <b>Vis</b> &quot;A2&quot;\n&amp; écrou "));
    }

    private function resolver(): MetaDescription
    {
        $translator = new Translator('fr_FR');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', [
            'description.default' => 'Démonstration B2B.',
            'description.sylius_shop_homepage' => 'Accueil de la démo.',
            'description.sylius_shop_product_index' => 'Catalogue %name% pour les professionnels.',
        ], 'fr_FR', 'seo');

        return new MetaDescription($translator);
    }
}
