<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-103: the guide to connect an assistant shows the transcript of a real conversation, dated, with an optional
 * recording; without a recording of the conversation the block is hidden and the guide stays; a transcript holding a
 * token, an e-mail address or a local address is never displayed; the address of the server is the one of the public site.
 */
final class McpTranscriptTest extends WebTestCase
{
    use EnvironmentSwitch;

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        @rmdir(\dirname(__DIR__, 3) . '/public/demo-media');
        parent::tearDown();
    }

    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'ne paie jamais'];
        yield 'English' => ['en_US', 'never pays'];
    }

    #[DataProvider('locales')]
    public function testTheTranscriptIsReadableDatedAndRemindsThatTheAssistantNeverPays(string $locale, string $reminder): void
    {
        $page = $this->open($locale, $this->transcript(5));

        self::assertResponseIsSuccessful();
        $block = $page->filter('[data-mcp-transcript]');
        self::assertCount(1, $block);
        self::assertCount(5, $block->filter('[data-mcp-exchange]'));
        self::assertSame('2026-11-05', $block->filter('time')->attr('datetime'));
        self::assertStringContainsString('Claude', $block->text());
        self::assertStringContainsString('list_my_orders', $block->filter('[data-mcp-exchange="1"] [data-mcp-tools]')->text());
        self::assertStringContainsString($reminder, $block->text());
        self::assertSame('fr', $block->filter('[data-mcp-exchange="1"] [lang]')->first()->attr('lang'));
        self::assertCount(6, $page->filter('[data-mcp-step]'), 'the existing guide stays');
    }

    public function testATranscriptWithoutRecordingIsShownAloneWithoutPlayerNorLink(): void
    {
        $page = $this->open('fr_FR', $this->transcript(4));

        self::assertCount(1, $page->filter('[data-mcp-transcript]'));
        self::assertCount(0, $page->filter('[data-mcp-transcript] figure, [data-mcp-transcript] video, [data-mcp-transcript] img, [data-mcp-transcript] button'));
        self::assertCount(0, $page->filter('[data-mcp-transcript] a'));
    }

    public function testAVideoHasControlsNoAutoplayAndItsTextAlternative(): void
    {
        $this->media('conversation.mp4');
        $page = $this->open('fr_FR', $this->transcript(4) . "media:\n    kind: video\n    src: /demo-media/conversation.mp4\n    description: \"Capture de la conversation avec l'assistant\"\n");

        $video = $page->filter('[data-mcp-transcript] video');
        self::assertCount(1, $video);
        self::assertNotNull($video->attr('controls'));
        self::assertNull($video->attr('autoplay'));
        self::assertSame('none', $video->attr('preload'));
        self::assertSame('/demo-media/conversation.mp4', $video->filter('source')->attr('src'));
        self::assertStringContainsString("Capture de la conversation avec l'assistant", $page->filter('[data-mcp-transcript] figcaption')->text());
    }

    public function testAnAnimationStartsFromAStillPosterAndPlaysOnRequest(): void
    {
        $this->media('conversation.gif');
        $this->media('conversation.png');
        $page = $this->open('en_US', $this->transcript(4) . "media:\n    kind: gif\n    src: /demo-media/conversation.gif\n    poster: /demo-media/conversation.png\n    description: \"Recording of the conversation\"\n");

        $image = $page->filter('[data-mcp-transcript] img[data-mcp-gif]');
        self::assertSame('/demo-media/conversation.png', $image->attr('src'), 'the page loads the still frame, not the animation');
        self::assertSame('/demo-media/conversation.gif', $image->attr('data-animation'));
        self::assertSame('Recording of the conversation', $image->attr('alt'));
        self::assertCount(1, $page->filter('[data-mcp-transcript] button[data-mcp-gif-toggle][aria-pressed="false"]'));
    }

    public function testWithoutAnyRecordedConversationTheBlockIsHiddenAndTheGuideStays(): void
    {
        $this->setEnvironment(['DEMO_MCP_TRANSCRIPT_FILE' => sys_get_temp_dir() . '/transcript-absent-' . bin2hex(random_bytes(4)) . '.yaml']);
        $page = self::createClient()->request('GET', '/fr_FR/mcp');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter('[data-mcp-transcript]'));
        self::assertCount(6, $page->filter('[data-mcp-step]'));
        self::assertCount(1, $page->filter('[data-mcp-server-url]'));
    }

    /** @return iterable<string, array{string}> */
    public static function sensitiveTexts(): iterable
    {
        yield 'token' => ['Authorization: Bearer 9f8e7d6c5b4a39281716'];
        yield 'e-mail address' => ['Écris à claire@acme.example'];
        yield 'local URL' => ['Va sur http://localhost:8080/_mcp'];
    }

    #[DataProvider('sensitiveTexts')]
    public function testATranscriptWithSensitiveDataIsNeverDisplayed(string $text): void
    {
        $page = $this->open('fr_FR', str_replace('Question 2', $text, $this->transcript(4)));

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter('[data-mcp-transcript]'));
        self::assertStringNotContainsString($text, (string) $page->html());
        self::assertCount(6, $page->filter('[data-mcp-step]'));
    }

    public function testTheServerAddressIsTheOneOfThePublicSiteWhateverTheAddressOfTheRequest(): void
    {
        $this->setEnvironment(['DEFAULT_URI' => 'https://sylius-b2b.exemple.test']);

        $page = self::createClient()->request('GET', '/fr_FR/mcp');

        self::assertSame('https://sylius-b2b.exemple.test/_mcp', trim($page->filter('[data-mcp-server-url]')->text()));
        self::assertStringNotContainsString('localhost', $page->filter('[data-mcp-config]')->text());
        self::assertStringContainsString('https://sylius-b2b.exemple.test/_mcp', $page->filter('[data-mcp-config]')->text());
        self::assertStringNotContainsString('localhost', $page->filter('[data-mcp-step="2"]')->text());
    }

    private function open(string $locale, string $transcript): Crawler
    {
        $file = tempnam(sys_get_temp_dir(), 'transcript') ?: throw new \RuntimeException('no temporary file');
        file_put_contents($file, $transcript);
        $this->files[] = $file;
        $this->setEnvironment(['DEMO_MCP_TRANSCRIPT_FILE' => $file]);

        return self::createClient()->request('GET', '/' . $locale . '/mcp');
    }

    private function media(string $name): void
    {
        $directory = \dirname(__DIR__, 3) . '/public/demo-media';
        is_dir($directory) || mkdir($directory, 0o777, true);
        file_put_contents($directory . '/' . $name, 'x');
        $this->files[] = $directory . '/' . $name;
    }

    private function transcript(int $exchanges): string
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
