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
            $this->hit('1', 'A cartoon of a hard hat'),
            $this->hit('2', 'Portrait of a woman with a hard hat'),
            $this->hit('3', 'Sunset over the sea'),
            $this->hit('4', 'Yellow hat'),
            $this->hit('5', 'Construction hard hat on a table'),
            $this->hit('6', 'Photo', [['name' => 'hard hat']], 'by'),
        ]);

        $ids = \array_map(static fn ($candidate): string => $candidate->id, $client->search('hard hat'));

        self::assertSame(['5', '4'], $ids, 'Best match first: both words of the query, then the head noun only.');
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
