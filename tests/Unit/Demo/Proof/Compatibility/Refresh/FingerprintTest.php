<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Compatibility\Refresh\CiWorkflow;
use App\Demo\Proof\Compatibility\Refresh\Fingerprint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * US-171: what the check compares to tell that a composer.json or a CI matrix changed since the last generation. The
 * same fingerprint comes from the files read through the API and from a local clone.
 */
final class FingerprintTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/compat-fingerprint-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->directory);
    }

    public function testTheFingerprintFollowsBothFiles(): void
    {
        $base = Fingerprint::of('{"a":1}', 'jobs: {}');

        self::assertSame($base, Fingerprint::of('{"a":1}', 'jobs: {}'));
        self::assertNotSame($base, Fingerprint::of('{"a":2}', 'jobs: {}'));
        self::assertNotSame($base, Fingerprint::of('{"a":1}', 'jobs: {x: 1}'));
        self::assertNotSame($base, Fingerprint::of('{"a":1}', null));
    }

    public function testALocalCloneGivesTheSameFingerprintAsTheApiFiles(): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->directory . '/composer.json', '{"a":1}');
        $filesystem->dumpFile($this->directory . '/.github/workflows/build.yaml', 'jobs: {}');
        $filesystem->dumpFile($this->directory . '/.github/workflows/other.yaml', 'jobs: {y: 1}');

        self::assertSame(Fingerprint::of('{"a":1}', 'jobs: {}'), Fingerprint::ofDirectory($this->directory));
    }

    public function testADirectoryWithoutComposerJsonHasNoFingerprint(): void
    {
        new Filesystem()->mkdir($this->directory);

        self::assertNull(Fingerprint::ofDirectory($this->directory));
    }

    public function testTheWorkflowReadIsTheSameOneEverywhere(): void
    {
        self::assertSame('build.yaml', CiWorkflow::preferred(['zz.yaml', 'build.yaml', 'ci.yaml']));
        self::assertSame('ci.yml', CiWorkflow::preferred(['ci.yml', 'other.yaml']));
        self::assertSame('a.yaml', CiWorkflow::preferred(['b.yaml', 'a.yaml']));
        self::assertNull(CiWorkflow::preferred([]));
    }
}
