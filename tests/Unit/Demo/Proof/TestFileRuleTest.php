<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof;

use App\Demo\Proof\TestFileRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/** US-168: one counting rule for the test files, shared by the refresh (GitHub tree) and the check (local tree). */
final class TestFileRuleTest extends TestCase
{
    /** @return iterable<string, array{string, bool}> */
    public static function paths(): iterable
    {
        yield 'unit test' => ['tests/Unit/MoneyTest.php', true];
        yield 'behat feature' => ['tests/Behat/quick_order.feature', true];
        yield 'helper of the suite' => ['tests/TestApplication/Kernel.php', false];
        yield 'test-named class outside tests/' => ['src/FooTest.php', false];
    }

    #[DataProvider('paths')]
    public function testItMatchesTheTestFilesOnly(string $path, bool $expected): void
    {
        self::assertSame($expected, TestFileRule::matches($path));
    }

    public function testItCountsTheTestFilesOfALocalTree(): void
    {
        $root = sys_get_temp_dir() . '/test-file-rule-' . bin2hex(random_bytes(4));
        $filesystem = new Filesystem();
        foreach (['tests/Unit/ATest.php', 'tests/Functional/Deep/BTest.php', 'tests/Behat/c.feature', 'tests/Unit/Helper.php', 'src/DTest.php'] as $path) {
            $filesystem->dumpFile($root . '/' . $path, '');
        }

        try {
            self::assertSame(3, TestFileRule::countIn($root));
        } finally {
            $filesystem->remove($root);
        }
    }

    public function testATreeWithoutTestsCountsZero(): void
    {
        self::assertSame(0, TestFileRule::countIn(sys_get_temp_dir() . '/no-such-tree-' . bin2hex(random_bytes(4))));
    }
}
