<?php

declare(strict_types=1);

namespace App\Demo\Mcp;

final readonly class TranscriptExchange
{
    /** @param list<string> $tools the MCP tools the assistant called to answer */
    public function __construct(public string $user, public string $assistant, public array $tools = [])
    {
    }
}
