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
 * US-192: the pending approvals of the member as a discreet signal on the greeting button: shown only for a member who may
 * approve and only above zero, read aloud with the button (hidden text), the number alone being hidden from assistive technologies.
 */
final class AccountBadgeTemplateTest extends TestCase
{
    /** @return iterable<string, array{int|null, bool}> the count given by the plugin (null: the member may not approve), whether a badge shows */
    public static function counts(): iterable
    {
        yield 'a member who may not approve' => [null, false];
        yield 'nothing pending' => [0, false];
        yield 'one pending' => [1, true];
        yield 'three pending' => [3, true];
        yield 'a hundred pending' => [120, true];
    }

    #[DataProvider('counts')]
    public function testTheBadgeShowsOnlyAboveZeroForAMemberWhoMayApprove(?int $count, bool $shown): void
    {
        $html = $this->render($count);

        self::assertSame($shown, str_contains($html, 'data-b2b-account-badge'));
    }

    public function testTheNumberIsVisualAndTheTextIsReadWithTheButton(): void
    {
        $html = $this->render(3);

        self::assertMatchesRegularExpression('/<span[^>]*aria-hidden="true"[^>]*data-b2b-account-badge[^>]*>\s*3\s*<\/span>/', $html);
        self::assertMatchesRegularExpression('/<span class="visually-hidden">[^<]*header\.pending:3[^<]*<\/span>/', $html);
    }

    public function testAHugeCountIsCappedOnTheBadgeButNotInTheSpokenText(): void
    {
        $html = $this->render(120);

        self::assertStringContainsString('99+', $html);
        self::assertStringContainsString('header.pending:120', $html);
    }

    private function render(?int $count): string
    {
        $twig = new Environment(new FilesystemLoader(\dirname(__DIR__, 3) . '/templates/shop/theme_b2b'), ['strict_variables' => true]);
        $twig->addFunction(new TwigFunction('tmonier_header_approvals', static fn (): ?int => $count));
        $twig->addFilter(new TwigFilter('trans', static fn (string $key, array $parameters = []): string => $key . ':' . ($parameters['%count%'] ?? '')));

        return $twig->render('_account_badge.html.twig');
    }
}
