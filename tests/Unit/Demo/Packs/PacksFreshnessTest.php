<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Packs;

use App\Demo\Packs\PackFingerprint;
use App\Demo\Packs\PackRepositories;
use App\Demo\Packs\PacksFreshness;
use PHPUnit\Framework\TestCase;

/**
 * US-178, same mechanism as the proof (US-168) and the matrix (US-171): the packs data against the repositories. The packs
 * the decision names and, when clones are at hand, the composer.json and READMEs they hold. No network, nothing written.
 */
final class PacksFreshnessTest extends TestCase
{
    public function testNothingIsBehindWhenEveryPackIsThereAndNoCloneDiffers(): void
    {
        self::assertSame([], PacksFreshness::gaps(PackSamples::data(), PackRepositories::ALL));
    }

    public function testAPackMissingFromTheDataIsAGapNamingIt(): void
    {
        $data = PackSamples::data();
        unset($data['packs'][PackSamples::OTHER_REPOSITORY]);

        $gaps = PacksFreshness::gaps($data, PackRepositories::ALL);

        self::assertCount(1, $gaps);
        self::assertStringContainsString(PackSamples::OTHER_REPOSITORY, $gaps[0]->describe());
        self::assertStringContainsString('packs.json', $gaps[0]->describe());
    }

    public function testAPackNoLongerExpectedIsAGapNamingIt(): void
    {
        $gaps = PacksFreshness::gaps(PackSamples::data(), [PackSamples::REPOSITORY]);

        self::assertCount(1, $gaps);
        self::assertStringContainsString(PackSamples::OTHER_REPOSITORY, $gaps[0]->describe());
    }

    public function testACloneWhoseFilesChangedSinceTheLastGenerationIsAGap(): void
    {
        $gaps = PacksFreshness::gaps(PackSamples::data(), PackRepositories::ALL, [PackSamples::REPOSITORY => 'changed']);

        self::assertCount(1, $gaps);
        self::assertStringContainsString(PackSamples::REPOSITORY, $gaps[0]->describe());
        self::assertStringContainsString('bin/packs-refresh', $gaps[0]->describe());
    }

    public function testACloneWithTheSameFingerprintIsNotAGap(): void
    {
        self::assertSame([], PacksFreshness::gaps(PackSamples::data(), PackRepositories::ALL, [PackSamples::REPOSITORY => 'a']));
    }

    public function testTheFingerprintOfADirectoryIsTheOneOfTheFilesTheApiReturns(): void
    {
        $directory = sys_get_temp_dir() . '/pack-fingerprint-' . bin2hex(random_bytes(4));
        mkdir($directory);
        file_put_contents($directory . '/composer.json', PackSamples::composer());
        file_put_contents($directory . '/README.md', PackSamples::readmeEn());
        file_put_contents($directory . '/README.fr.md', PackSamples::readmeFr());

        try {
            self::assertSame(PackFingerprint::of(PackSamples::composer(), PackSamples::readmeEn(), PackSamples::readmeFr()), PackFingerprint::ofDirectory($directory));
            self::assertNull(PackFingerprint::ofDirectory($directory . '-absent'));
        } finally {
            array_map('unlink', glob($directory . '/*') ?: []);
            rmdir($directory);
        }
    }

    public function testTheFingerprintChangesWithAnyOfTheThreeFiles(): void
    {
        $base = PackFingerprint::of('c', 'e', 'f');

        self::assertNotSame($base, PackFingerprint::of('c2', 'e', 'f'));
        self::assertNotSame($base, PackFingerprint::of('c', 'e2', 'f'));
        self::assertNotSame($base, PackFingerprint::of('c', 'e', 'f2'));
    }
}
