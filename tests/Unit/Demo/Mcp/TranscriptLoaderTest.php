<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mcp;

use App\Demo\Mcp\InvalidTranscript;
use App\Demo\Mcp\TranscriptLoader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * US-103: the transcript of a real conversation with an assistant is a versioned data file, checked when it is loaded.
 * No file (or an empty one) means the block stays hidden; a file with a token, an e-mail address or a local URL is
 * refused with the line and the reason; a media file that is not there is dropped, the transcript is shown alone.
 */
final class TranscriptLoaderTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/transcript-' . bin2hex(random_bytes(4));
        mkdir($this->directory . '/public/demo-media', 0o777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/public/demo-media/*') ?: [] as $file) {
            unlink($file);
        }
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            is_file($file) && unlink($file);
        }
        rmdir($this->directory . '/public/demo-media');
        rmdir($this->directory . '/public');
        rmdir($this->directory);
    }

    public function testAValidTranscriptIsLoadedWithItsDateItsAssistantAndItsExchanges(): void
    {
        $transcript = $this->load($this->yaml());

        self::assertNotNull($transcript);
        self::assertSame('2026-11-05', $transcript->recordedAt);
        self::assertSame('fr', $transcript->locale);
        self::assertSame('Claude', $transcript->assistant);
        self::assertCount(4, $transcript->exchanges);
        self::assertSame(['list_my_orders'], $transcript->exchanges[0]->tools);
        self::assertSame('Liste mes dernières commandes.', $transcript->exchanges[0]->user);
        self::assertNull($transcript->media, 'a transcript without media is shown alone');
    }

    public function testNoFileOrAnEmptyFileMeansNoTranscript(): void
    {
        self::assertNull((new TranscriptLoader($this->directory . '/absent.yaml', $this->directory . '/public'))->load());
        self::assertNull($this->load(''));
        self::assertNull($this->load("# nothing recorded yet\n"));
    }

    /** @return iterable<string, array{int}> */
    public static function wrongNumbersOfExchanges(): iterable
    {
        yield 'three' => [3];
        yield 'seven' => [7];
    }

    #[DataProvider('wrongNumbersOfExchanges')]
    public function testFourToSixExchangesAreRequired(int $count): void
    {
        $this->expectException(InvalidTranscript::class);
        $this->expectExceptionMessage('4 to 6');

        $this->load($this->yaml($count));
    }

    /** @return iterable<string, array{string, string}> */
    public static function sensitiveLines(): iterable
    {
        yield 'e-mail address' => ['Écris à claire@acme.example pour confirmer.', 'e-mail'];
        yield 'bearer token' => ['Authorization: Bearer 9f8e7d6c5b4a39281716', 'token'];
        yield 'JWT' => ['voici eyJhbGciOiJFZERTQSJ9.eyJzdWIiOiIxMjMifQ.c2lnbmF0dXJl', 'token'];
        yield 'access token in a URL' => ['https://exemple.test/cb?access_token=abcdef1234567890', 'token'];
        yield 'long opaque string' => ['la clé est 0123456789abcdef0123456789abcdef0123', 'token'];
        yield 'localhost' => ['Le serveur répond sur http://localhost:8080/_mcp', 'local'];
        yield 'loopback address' => ['Essaie 127.0.0.1 en local', 'local'];
        yield 'local domain' => ['sur demo.local', 'local'];
    }

    #[DataProvider('sensitiveLines')]
    public function testASensitiveLineRefusesTheWholeTranscriptNamingTheLineAndTheReason(string $text, string $reason): void
    {
        $yaml = str_replace('Liste mes dernières commandes.', $text, $this->yaml());
        $line = array_keys(array_filter(explode("\n", $yaml), static fn (string $line): bool => str_contains($line, $text)))[0] + 1;

        try {
            $this->load($yaml);
            self::fail('The transcript must be refused.');
        } catch (InvalidTranscript $exception) {
            self::assertStringContainsString('line ' . $line, $exception->getMessage());
            self::assertStringContainsString($reason, $exception->getMessage());
            self::assertSame($line, $exception->lineNumber);
        }
    }

    public function testAnOrdinaryUrlOfThePublicSiteIsAccepted(): void
    {
        $yaml = str_replace('Liste mes dernières commandes.', 'Connecte-toi à https://sylius-b2b.thibaut-monier.fr/_mcp', $this->yaml());

        self::assertCount(4, $this->load($yaml)->exchanges ?? []);
    }

    /** @return iterable<string, array{string, string}> */
    public static function brokenStructures(): iterable
    {
        yield 'unknown language' => ["locale: fr\n", "locale: de\n"];
        yield 'impossible date' => ["recorded_at: '2026-11-05'\n", "recorded_at: '2026-13-45'\n"];
        yield 'no assistant' => ["assistant: Claude\n", "assistant: ''\n"];
    }

    #[DataProvider('brokenStructures')]
    public function testABrokenStructureIsRefused(string $search, string $replace): void
    {
        $this->expectException(InvalidTranscript::class);

        $this->load(str_replace($search, $replace, $this->yaml()));
    }

    public function testAnExchangeWithoutAnswerIsRefused(): void
    {
        $this->expectException(InvalidTranscript::class);
        $this->expectExceptionMessage('exchange 2');

        $this->load(str_replace("    assistant: \"Réponse 2\"\n", '', $this->yaml()));
    }

    public function testAVideoOfThePublicFolderIsKeptWithItsTextAlternative(): void
    {
        file_put_contents($this->directory . '/public/demo-media/conversation.mp4', 'video');

        $media = $this->load($this->yaml() . "media:\n    kind: video\n    src: /demo-media/conversation.mp4\n    description: \"Capture de la conversation\"\n")?->media;

        self::assertNotNull($media);
        self::assertSame('video', $media->kind);
        self::assertSame('/demo-media/conversation.mp4', $media->src);
        self::assertSame('Capture de la conversation', $media->description);
    }

    public function testAMediaFileThatIsNotThereIsDroppedAndTheTranscriptStaysAlone(): void
    {
        $transcript = $this->load($this->yaml() . "media:\n    kind: video\n    src: /demo-media/absent.mp4\n    description: \"Capture\"\n");

        self::assertNotNull($transcript);
        self::assertNull($transcript->media);
    }

    public function testAGifNeedsAStillPoster(): void
    {
        file_put_contents($this->directory . '/public/demo-media/conversation.gif', 'gif');
        $gif = "media:\n    kind: gif\n    src: /demo-media/conversation.gif\n    description: \"Capture\"\n";

        self::assertNull($this->load($this->yaml() . $gif)?->media, 'an animation without a still poster cannot be paused: dropped');

        file_put_contents($this->directory . '/public/demo-media/conversation.png', 'png');
        $media = $this->load($this->yaml() . $gif . "    poster: /demo-media/conversation.png\n")?->media;
        self::assertSame('/demo-media/conversation.png', $media?->poster);
    }

    /** @return iterable<string, array{string}> */
    public static function unsafeMediaPaths(): iterable
    {
        yield 'outside the folder' => ['/etc/passwd'];
        yield 'parent directory' => ['/demo-media/../.env'];
        yield 'external URL' => ['https://exemple.test/video.mp4'];
        yield 'wrong extension for a video' => ['/demo-media/conversation.exe'];
    }

    #[DataProvider('unsafeMediaPaths')]
    public function testAMediaOutsideThePublicFolderIsRefused(string $path): void
    {
        $this->expectException(InvalidTranscript::class);

        $this->load($this->yaml() . "media:\n    kind: video\n    src: " . $path . "\n    description: \"Capture\"\n");
    }

    public function testAMediaWithoutTextAlternativeIsRefused(): void
    {
        file_put_contents($this->directory . '/public/demo-media/conversation.mp4', 'video');
        $this->expectException(InvalidTranscript::class);
        $this->expectExceptionMessage('description');

        $this->load($this->yaml() . "media:\n    kind: video\n    src: /demo-media/conversation.mp4\n");
    }

    private function load(string $yaml): ?\App\Demo\Mcp\Transcript
    {
        $file = $this->directory . '/transcript.yaml';
        file_put_contents($file, $yaml);

        return (new TranscriptLoader($file, $this->directory . '/public'))->load();
    }

    private function yaml(int $exchanges = 4): string
    {
        $yaml = "recorded_at: '2026-11-05'\nlocale: fr\nassistant: Claude\nexchanges:\n";
        for ($number = 1; $number <= $exchanges; ++$number) {
            $yaml .= '  - user: "' . (1 === $number ? 'Liste mes dernières commandes.' : 'Question ' . $number) . "\"\n";
            $yaml .= 1 === $number ? "    tools: [list_my_orders]\n" : '';
            $yaml .= '    assistant: "Réponse ' . $number . "\"\n";
        }

        return $yaml;
    }
}
