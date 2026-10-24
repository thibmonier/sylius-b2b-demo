<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Home;

use App\Demo\Home\HomePhotos;
use App\Tests\Support\HomePhotoAudit;
use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-186: the photos of the home are credited, light, sized and described; the audit itself is proven on a bad directory. */
final class HomePhotosTest extends TestCase
{
    private const SLOTS = ['hero', 'epi', 'quincaillerie', 'industriel', 'parcours', 'acheteur', 'approbatrice', 'administrateur'];

    private string $directory = '';

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/home_photos_' . uniqid('', true);
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->directory);
    }

    public function testEveryPhotoOfTheHomeIsCreditedLightAndSized(): void
    {
        self::assertSame([], HomePhotoAudit::problems(self::directory()));
    }

    public function testTheCatalogueHoldsTheEightSelectedPhotos(): void
    {
        $photos = new HomePhotos(self::directory() . '/credits.json');

        self::assertSame(self::SLOTS, array_map(static fn ($photo): string => $photo->slot, $photos->all()));
        foreach ($photos->all() as $photo) {
            self::assertSame('Pexels License', $photo->license, $photo->slot);
            self::assertStringContainsString('pexels.com/photo/', $photo->originUrl, $photo->slot);
            self::assertGreaterThanOrEqual(2, \count($photo->files), $photo->slot);
            self::assertGreaterThan($photo->smallest()->width, $photo->largest()->width, $photo->slot);
        }
    }

    public function testASlotThatDoesNotExistIsRefusedByItsName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('banana');

        (new HomePhotos(self::directory() . '/credits.json'))->get('banana');
    }

    public function testEveryPhotoHasATextAlternativeInBothLanguages(): void
    {
        foreach (['fr', 'en'] as $language) {
            $texts = Yaml::parseFile(\dirname(__DIR__, 4) . '/translations/home.' . $language . '.yaml');
            foreach (self::SLOTS as $slot) {
                $alt = Typed::find($texts, 'photo', $slot, 'alt');
                self::assertIsString($alt, $language . ' alt of ' . $slot);
                self::assertNotSame('', trim($alt), $language . ' alt of ' . $slot);
            }
        }
    }

    public function testAPhotoWithoutCreditIsReportedByItsFileName(): void
    {
        copy(self::directory() . '/hero-480.webp', $this->directory . '/orphan-480.webp');
        file_put_contents($this->directory . '/credits.json', json_encode(['photos' => []]));

        $problems = HomePhotoAudit::problems($this->directory);

        self::assertContains('orphan-480.webp: no credit (source, author, licence) in credits.json', $problems);
    }

    public function testACreditWithoutAuthorOrLicenceIsReported(): void
    {
        copy(self::directory() . '/hero-480.webp', $this->directory . '/a-480.webp');
        copy(self::directory() . '/hero-800.webp', $this->directory . '/a-800.webp');
        file_put_contents($this->directory . '/credits.json', json_encode(['photos' => [[
            'slot' => 'a', 'source' => 'Pexels', 'origin_url' => 'https://www.pexels.com/photo/a/', 'creator' => '', 'license' => '', 'license_url' => 'http://insecure',
            'files' => [['file' => 'a-480.webp', 'width' => 480, 'height' => 600], ['file' => 'a-800.webp', 'width' => 800, 'height' => 1000]],
        ]]]));

        $problems = HomePhotoAudit::problems($this->directory);

        self::assertContains('a: the credit has no creator', $problems);
        self::assertContains('a: the credit has no license', $problems);
        self::assertContains('a: license_url is not an https URL', $problems);
    }

    public function testAnImageThatIsTooHeavyOrNotModernOrMisSizedIsReportedByItsFileName(): void
    {
        file_put_contents($this->directory . '/big-800.png', str_repeat('x', HomePhotoAudit::MAX_BYTES + 1));
        copy(self::directory() . '/hero-480.webp', $this->directory . '/big-480.webp');
        file_put_contents($this->directory . '/credits.json', json_encode(['photos' => [[
            'slot' => 'big', 'source' => 'Pexels', 'origin_url' => 'https://www.pexels.com/photo/b/', 'creator' => 'X', 'license' => 'L', 'license_url' => 'https://l.example/',
            'files' => [['file' => 'big-480.webp', 'width' => 999, 'height' => 999], ['file' => 'big-800.png', 'width' => 800, 'height' => 600]],
        ]]]));

        $problems = HomePhotoAudit::problems($this->directory);

        self::assertContains('big-800.png: not a modern format (WebP expected)', $problems);
        self::assertContains('big-800.png: heavier than 250000 bytes', $problems);
        self::assertContains('big-480.webp: the dimensions of credits.json do not match the file', $problems);
    }

    private static function directory(): string
    {
        return \dirname(__DIR__, 4) . '/assets/shop/images/home';
    }
}
