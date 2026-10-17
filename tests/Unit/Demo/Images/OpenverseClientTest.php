<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\Fetch\OpenverseClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class OpenverseClientTest extends TestCase
{
    public function testTheResultsThatDoNotShowTheArticleOrShowPeopleAndDrawingsAreDropped(): void
    {
        $client = $this->client([
            $this->hit('00000001-0000-0000-0000-000000000000', 'A cartoon of a hard hat'),
            $this->hit('00000002-0000-0000-0000-000000000000', 'Portrait of a woman with a hard hat'),
            $this->hit('00000003-0000-0000-0000-000000000000', 'Sunset over the sea'),
            $this->hit('00000004-0000-0000-0000-000000000000', 'Yellow hat'),
            $this->hit('00000005-0000-0000-0000-000000000000', 'Construction hard hat on a table'),
            $this->hit('00000006-0000-0000-0000-000000000000', 'Photo', [['name' => 'hard hat']], 'by'),
        ]);

        $ids = \array_map(static fn ($candidate): string => $candidate->id, $client->search('hard hat'));

        self::assertSame(['00000005-0000-0000-0000-000000000000', '00000004-0000-0000-0000-000000000000'], $ids, 'Best match first: both words of the query, then the head noun only.');
    }

    public function testAnIdThatIsNotAUuidIsRejectedBecauseItNamesTheFile(): void
    {
        $client = $this->client([
            $this->hit('../../public/index', 'Hard hat'),
            $this->hit('', 'Hard hat'),
            $this->hit('aaaa1111-2222-3333-4444-555566667777', 'Hard hat'),
        ]);

        $ids = \array_map(static fn ($candidate): string => $candidate->id, $client->search('hard hat'));

        self::assertSame(['aaaa1111-2222-3333-4444-555566667777'], $ids);
    }

    public function testTheSearchIsMadeWithTheCc0FilterAndAnIdentifiedUserAgent(): void
    {
        $seen = [];
        $http = new MockHttpClient(static function (string $method, string $url, array $options) use (&$seen): MockResponse {
            $seen[] = [$url, $options['headers']];

            return new MockResponse('{"results": []}');
        });

        (new OpenverseClient($http, 'my-agent/1.0'))->search('hex bolt');

        self::assertStringContainsString('license=cc0', $seen[0][0]);
        self::assertContains('User-Agent: my-agent/1.0', $seen[0][1]);
        self::assertStringNotContainsString('category=photograph', $seen[1][0], 'Few results: the second search drops the category.');
    }

    /** @param list<array<string, mixed>> $results */
    private function client(array $results): OpenverseClient
    {
        return new OpenverseClient(new MockHttpClient(static fn (): MockResponse => new MockResponse((string) \json_encode(['results' => $results]))), 'agent/1.0');
    }

    /**
     * @param list<array{name: string}> $tags
     *
     * @return array<string, mixed>
     */
    private function hit(string $id, string $title, array $tags = [], string $license = 'cc0'): array
    {
        return [
            'id' => $id, 'title' => $title, 'tags' => $tags, 'license' => $license, 'creator' => 'x',
            'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/', 'url' => 'https://img.test/' . $id . '.jpg',
            'foreign_landing_url' => 'https://example.test/' . $id, 'source' => 'flickr', 'filetype' => 'jpg',
        ];
    }
}
