<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * US-192: the "Approvals" entry of the account menu is the entry of the purchase-approval plugin (phone layout): its badge
 * shows above zero only, and the number is read with the entry. Rendered here with stubs for the router and the translator.
 */
final class ApprovalsMenuEntryTest extends TestCase
{
    /** @return iterable<string, array{int, bool}> */
    public static function counts(): iterable
    {
        yield 'nothing pending' => [0, false];
        yield 'one pending' => [1, true];
        yield 'three pending' => [3, true];
    }

    #[DataProvider('counts')]
    public function testTheBadgeShowsAboveZeroOnly(int $count, bool $badge): void
    {
        $html = $this->render($count);

        self::assertSame($badge, str_contains($html, 'badge text-bg-warning'));
        if ($badge) {
            self::assertMatchesRegularExpression('/aria-hidden="true"[^>]*>\s*' . $count . '\s*<\/span>/', $html);
        }
    }

    public function testTheNumberIsReadWithTheEntry(): void
    {
        $html = $this->render(3);

        self::assertMatchesRegularExpression('/<span class="visually-hidden">header\.pending:3<\/span>/', $html);
        self::assertStringContainsString('tmonier_purchase_approval.ui.approvals', $html);
    }

    private function render(int $count): string
    {
        $loader = new FilesystemLoader();
        $loader->addPath(\dirname(__DIR__, 3) . '/vendor/tmonier/sylius-purchase-approval-plugin/templates', 'TmonierSyliusPurchaseApprovalPlugin');
        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addFunction(new TwigFunction('path', static fn (string $name): string => '/' . $name));
        $twig->addFunction(new TwigFunction('sylius_test_html_attribute', static fn (): string => ''));
        $twig->addFilter(new TwigFilter('trans', static fn (string $key, array $parameters = []): string => str_ends_with($key, 'header.pending') ? 'header.pending:' . $parameters['%count%'] : $key));

        return $twig->render('@TmonierSyliusPurchaseApprovalPlugin/shop/header/_entry.html.twig', ['count' => $count, 'layout' => 'mobile']);
    }
}
