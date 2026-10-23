<?php

declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * US-166: tests never wait on the real time. A wait goes through App\Tests\Support\Await (a condition with a
 * deadline, a clock that can be replaced); a bare sleep()/usleep() call makes a suite slow and flaky.
 */
final class NoSleepInTestsTest extends TestCase
{
    private const FORBIDDEN = ['sleep', 'usleep', 'time_nanosleep', 'time_sleep_until'];

    public function testNoTestCallsSleepOrUsleep(): void
    {
        self::assertSame([], $this->sleepCalls(\dirname(__DIR__, 2)), 'Use App\Tests\Support\Await instead of sleeping.');
    }

    public function testTheScanFindsACallButNotAWordInAStringOrAMethodName(): void
    {
        $dir = sys_get_temp_dir() . '/no-sleep-' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir . '/BadTest.php', "<?php\n\nusleep(10);\n\\sleep(1);\n\$a = 'usleep(1)';\n\$this->sleep(1);\nClock::sleep(1);\n");

        try {
            $found = $this->sleepCalls($dir);
        } finally {
            unlink($dir . '/BadTest.php');
            rmdir($dir);
        }

        self::assertSame(['BadTest.php:3: usleep()', 'BadTest.php:4: sleep()'], $found);
    }

    /** @return list<string> one "file:line: function()" entry per call to a function that blocks */
    private function sleepCalls(string $directory): array
    {
        $found = [];
        foreach (new Finder()->files()->in($directory)->name('*.php')->sortByName() as $file) {
            $tokens = \PhpToken::tokenize($file->getContents());
            foreach ($tokens as $index => $token) {
                $name = strtolower(ltrim($token->text, '\\'));
                if (!$token->is([\T_STRING, \T_NAME_FULLY_QUALIFIED]) || !\in_array($name, self::FORBIDDEN, true)) {
                    continue;
                }
                if ($this->isFunctionCall($tokens, $index)) {
                    $found[] = $file->getRelativePathname() . ':' . $token->line . ': ' . $name . '()';
                }
            }
        }

        return $found;
    }

    /** @param array<int, \PhpToken> $tokens */
    private function isFunctionCall(array $tokens, int $index): bool
    {
        $next = $this->neighbour($tokens, $index, 1);
        $previous = $this->neighbour($tokens, $index, -1);

        return null !== $next && '(' === $next->text &&
            !($previous?->is([\T_OBJECT_OPERATOR, \T_NULLSAFE_OBJECT_OPERATOR, \T_DOUBLE_COLON, \T_FUNCTION, \T_NEW]) ?? false);
    }

    /** @param array<int, \PhpToken> $tokens */
    private function neighbour(array $tokens, int $index, int $step): ?\PhpToken
    {
        for ($i = $index + $step; isset($tokens[$i]); $i += $step) {
            if (!$tokens[$i]->is([\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT])) {
                return $tokens[$i];
            }
        }

        return null;
    }
}
