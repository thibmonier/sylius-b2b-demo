<?php

declare(strict_types=1);

namespace App\Tests\Unit\Railway;

use PHPUnit\Framework\TestCase;

final class RailwayMediaVolumeTest extends TestCase
{
    private string $iac;

    protected function setUp(): void
    {
        $this->iac = (string) file_get_contents(\dirname(__DIR__, 3) . '/.railway/railway.ts');
    }

    public function testTheVolumeSdkHelperIsImported(): void
    {
        self::assertMatchesRegularExpression('/^import \{[^}]*\bvolume\b[^}]*\} from "railway\/iac";$/m', $this->iac);
    }

    public function testTheMediaVolumeIsDeclaredAsAProjectResource(): void
    {
        self::assertMatchesRegularExpression('/const mediaVolume = volume\("media", \{[^}]*\}\);/', $this->iac);
        self::assertMatchesRegularExpression('/resources: \[[^\]]*\bmediaVolume\b[^\]]*\]/', $this->iac);
    }

    public function testTheWebServiceMountsTheVolumeOnTheMediaDirectory(): void
    {
        self::assertMatchesRegularExpression('/^const MEDIA_MOUNT_PATH = "\/app\/public\/media";$/m', $this->iac);
        self::assertMatchesRegularExpression('/volumeMounts: \{ \[MEDIA_MOUNT_PATH\]: mediaVolume \}/', $this->iac);
    }

    public function testTheMountPathMatchesTheDirectoryServedByTheApplication(): void
    {
        $project = \dirname(__DIR__, 3);
        $dockerfile = (string) file_get_contents($project . '/docker/Dockerfile');

        self::assertStringContainsString('WORKDIR /app', $dockerfile);
        self::assertStringContainsString('%kernel.project_dir%/public', (string) file_get_contents($project . '/config/packages/liip_imagine.yaml'));
        self::assertStringContainsString('media/cache', (string) file_get_contents($project . '/config/packages/liip_imagine.yaml'));
    }

    public function testNoIdentifierIsWrittenInTheFile(): void
    {
        self::assertDoesNotMatchRegularExpression('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $this->iac);
        self::assertDoesNotMatchRegularExpression('/\bvol[_-][A-Za-z0-9]+/', $this->iac);
    }
}
