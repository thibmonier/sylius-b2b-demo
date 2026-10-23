<?php

declare(strict_types=1);

namespace App\Tests\Unit\Template;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Twig\Environment;

/**
 * US-098: the fill preview of the product page keeps the containers the quantity actually fills in sight; the others
 * go in a native, collapsed "details" (keyboard and no-JavaScript friendly).
 */
final class ContainerFillPreviewRowsTest extends KernelTestCase
{
    public function testOnlyTheContainersWithAFillAreVisibleAndTheOthersAreCollapsed(): void
    {
        $html = $this->render($this->rows([42.5, 12.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0]));

        self::assertSame(2, substr_count($html, 'data-test-tmonier-container-fill-preview-row'));
        self::assertSame(7, substr_count($html, 'data-test-tmonier-container-fill-preview-other'));
        self::assertMatchesRegularExpression('#<details(?![^>]*\bopen\b)[^>]*>\s*<summary[^>]*>[^<]*\(7\)#', $html);
        self::assertStringContainsString('0 % d’un(e) Type 3', $html);
    }

    public function testNoDetailsControlWhenEveryContainerIsFilled(): void
    {
        $html = $this->render($this->rows(array_fill(0, 9, 8.0)));

        self::assertSame(9, substr_count($html, 'data-test-tmonier-container-fill-preview-row'));
        self::assertStringNotContainsString('<details', $html);
        self::assertStringNotContainsString('<summary', $html);
    }

    public function testNoDataAtAllRendersNothing(): void
    {
        self::assertSame('', trim($this->render([])));
    }

    public function testWhenNothingIsFilledEverythingIsInTheDetails(): void
    {
        $html = $this->render($this->rows([0.0, 0.0]));

        self::assertSame(0, substr_count($html, 'data-test-tmonier-container-fill-preview-row'));
        self::assertSame(2, substr_count($html, 'data-test-tmonier-container-fill-preview-other'));
        self::assertSame(1, substr_count($html, '<details'));
    }

    /**
     * @param list<float> $percents
     *
     * @return list<array<string, mixed>>
     */
    private function rows(array $percents): array
    {
        $rows = [];
        foreach ($percents as $index => $percent) {
            $rows[] = ['layers' => null, 'name' => 'Type ' . ($index + 1), 'kind' => 'pallet', 'capacity' => 100, 'percent' => $percent, 'count' => round($percent / 100, 1), 'containers' => 1, 'indicative' => true, 'unitsPerLayer' => null];
        }

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows */
    private function render(array $rows): string
    {
        self::bootKernel();
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);
        $translator = self::getContainer()->get('translator');
        self::assertInstanceOf(LocaleAwareInterface::class, $translator);
        $translator->setLocale('fr_FR');

        return $twig->render('shop/container_fill/_preview_rows.html.twig', ['rows' => $rows, 'quantity' => 3]);
    }
}
