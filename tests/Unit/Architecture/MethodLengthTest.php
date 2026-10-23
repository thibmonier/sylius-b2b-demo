<?php

declare(strict_types=1);

namespace App\Tests\Unit\Architecture;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * US-166: no method of src/ is longer than 45 lines (signature and braces included), so a long method is split
 * before it reaches review instead of being found by the next audit.
 */
final class MethodLengthTest extends TestCase
{
    private const MAX_LINES = 45;

    public function testNoMethodOfSrcIsLongerThanTheLimit(): void
    {
        self::assertSame([], $this->tooLongMethods(\dirname(__DIR__, 3) . '/src'), 'Methods longer than ' . self::MAX_LINES . ' lines must be split.');
    }

    public function testTheMeasureReportsFileMethodAndLineCount(): void
    {
        $dir = sys_get_temp_dir() . '/method-length-' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir . '/Long.php', "<?php\n\nfinal class Long\n{\n    public function run(): void\n    {\n" . str_repeat("        \$a = 1;\n", self::MAX_LINES) . "    }\n}\n");

        try {
            $found = $this->tooLongMethods($dir);
        } finally {
            unlink($dir . '/Long.php');
            rmdir($dir);
        }

        self::assertSame(['Long.php: Long::run() has ' . (self::MAX_LINES + 3) . ' lines'], $found);
    }

    /** @return list<string> one "file: Class::method() has N lines" entry per method over the limit */
    private function tooLongMethods(string $directory): array
    {
        $parser = new ParserFactory()->createForHostVersion();
        $finder = new NodeFinder();
        $found = [];

        foreach (new Finder()->files()->in($directory)->name('*.php')->sortByName() as $file) {
            $statements = $parser->parse($file->getContents()) ?? [];
            foreach ($finder->findInstanceOf($statements, ClassMethod::class) as $method) {
                $lines = $method->getEndLine() - $method->getStartLine() + 1;
                if ($lines > self::MAX_LINES) {
                    $found[] = $file->getRelativePathname() . ': ' . $this->owner($statements, $method) . '::' . $method->name->toString() . '() has ' . $lines . ' lines';
                }
            }
        }

        return $found;
    }

    /** @param array<\PhpParser\Node\Stmt> $statements */
    private function owner(array $statements, ClassMethod $method): string
    {
        foreach (new NodeFinder()->findInstanceOf($statements, \PhpParser\Node\Stmt\Class_::class) as $class) {
            if ($class->getStartLine() <= $method->getStartLine() && $class->getEndLine() >= $method->getEndLine()) {
                return $class->name?->toString() ?? 'class@anonymous';
            }
        }

        return 'unknown';
    }
}
