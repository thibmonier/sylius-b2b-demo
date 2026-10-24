<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use App\Demo\Mail\MailTwigExtension;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-193: the excerpt of the page "Flat-file exchange" is the last file really deposited in the exchange directory: its header and
 * three lines of the current channel, the e-mails masked like in the demo mailbox. It only reads. Whatever goes wrong (no directory,
 * not readable, no file, an empty file, no line for the channel), the answer is a neutral status, never an exception nor a path.
 */
final readonly class ExchangeFileSample
{
    private const LINES = 3;

    public function __construct(
        #[Autowire(param: 'tmonier_erp_flatfile.exchange_directory')]
        private string $directory,
        private ExportedOrderChannels $channels,
    ) {
    }

    public function forChannel(string $channelCode): ExchangeFileExcerpt
    {
        $lines = $this->lastFileLines();
        if ($lines instanceof ExchangeFileStatus) {
            return new ExchangeFileExcerpt($lines);
        }

        $header = array_shift($lines);
        $own = $this->ofChannel($lines, $channelCode);
        if ([] === $own) {
            return new ExchangeFileExcerpt(ExchangeFileStatus::OtherChannel);
        }

        return new ExchangeFileExcerpt(ExchangeFileStatus::Shown, MailTwigExtension::maskAddresses(implode("\n", [$header, ...\array_slice($own, 0, self::LINES)])) . "\n");
    }

    /** @return non-empty-list<string>|ExchangeFileStatus the header then the lines of the last file, or why there is none */
    private function lastFileLines(): array|ExchangeFileStatus
    {
        if (!is_dir($this->directory) || !is_readable($this->directory)) {
            return ExchangeFileStatus::Unavailable;
        }
        $files = glob(rtrim($this->directory, '/') . '/*.csv') ?: [];
        if ([] === $files) {
            return ExchangeFileStatus::NoFile;
        }
        usort($files, static fn (string $a, string $b): int => [(int) filemtime($b), $b] <=> [(int) filemtime($a), $a]);

        $content = @file_get_contents($files[0]);
        if (false === $content) {
            return ExchangeFileStatus::Unavailable;
        }
        $lines = array_values(array_filter(explode("\n", str_replace("\r\n", "\n", $content)), static fn (string $line): bool => '' !== trim($line)));

        return \count($lines) < 2 ? ExchangeFileStatus::Empty : $lines;
    }

    /**
     * @param list<string> $lines
     *
     * @return list<string> the lines whose order was placed on the channel
     */
    private function ofChannel(array $lines, string $channelCode): array
    {
        $ids = array_map(static fn (string $line): string => explode(';', $line)[1] ?? '', $lines);
        $channels = $this->channels->channelsOf(array_values(array_filter($ids, static fn (string $id): bool => '' !== $id)));

        return array_values(array_filter($lines, static fn (string $line): bool => ($channels[explode(';', $line)[1] ?? ''] ?? null) === $channelCode));
    }
}
