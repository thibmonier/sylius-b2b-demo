<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Check;

use App\Demo\Proof\Check\ProofFile;
use App\Demo\Proof\Check\UnreadableProof;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/** US-168: a missing or malformed proof.json is a clear error, never a stack trace nor a silent pass. */
final class ProofFileTest extends TestCase
{
    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->files);
    }

    public function testAMissingFileIsUnreadable(): void
    {
        $this->expectException(UnreadableProof::class);
        $this->expectExceptionMessage('is missing');

        ProofFile::read(sys_get_temp_dir() . '/no-proof-' . bin2hex(random_bytes(4)) . '.json');
    }

    public function testInvalidJsonIsUnreadable(): void
    {
        $this->expectException(UnreadableProof::class);
        $this->expectExceptionMessage('is not valid JSON');

        ProofFile::read($this->file('{"plugins": '));
    }

    public function testAFileWithoutPluginsIsUnreadable(): void
    {
        $this->expectException(UnreadableProof::class);
        $this->expectExceptionMessage('has no "plugins" object');

        ProofFile::read($this->file('{"delivery": {}}'));
    }

    public function testAWellFormedFileIsReturnedDecoded(): void
    {
        self::assertSame(['plugins' => ['a' => []], 'delivery' => []], ProofFile::read($this->file('{"plugins": {"a": []}, "delivery": {}}')));
    }

    private function file(string $content): string
    {
        $path = sys_get_temp_dir() . '/proof-file-' . bin2hex(random_bytes(4)) . '.json';
        file_put_contents($path, $content);
        $this->files[] = $path;

        return $path;
    }
}
