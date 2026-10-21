<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mcp;

use App\Demo\Mcp\TranscriptLoader;
use PHPUnit\Framework\TestCase;

/**
 * US-103: the transcript committed with the demo (config/demo/mcp-transcript.yaml, added once Thibaut has recorded the
 * conversation) must pass the same checks as at runtime, so that a token or an e-mail address never reaches the production.
 * While the file does not exist, the block is hidden and there is nothing to check.
 */
final class CommittedTranscriptTest extends TestCase
{
    public function testTheCommittedTranscriptIsAcceptedByTheLoader(): void
    {
        $root = \dirname(__DIR__, 4);
        $file = $root . '/config/demo/mcp-transcript.yaml';
        if (!is_file($file)) {
            $this->addToAssertionCount(1);

            return;
        }

        $transcript = (new TranscriptLoader($file, $root . '/public'))->load();

        self::assertNotNull($transcript, 'an existing transcript file must hold a conversation, or be removed');
    }
}
