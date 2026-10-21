<?php

declare(strict_types=1);

namespace App\Demo\Mcp;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads and checks the transcript of a real conversation with an assistant (US-103, config/demo/mcp-transcript.yaml).
 *
 * No file, or a file with comments only, means no transcript (the block stays hidden). A file holding a token, an e-mail
 * address or a local address is refused as a whole with the line and the reason (InvalidTranscript). The media files live
 * in public/demo-media/: one that is not there is dropped, so the page never shows an empty player or a broken link.
 */
final class TranscriptLoader
{
    private const MEDIA_DIRECTORY = '/demo-media/';

    private const LOCALES = ['fr', 'en'];

    /** @var array<string, string> reason => pattern, checked line by line on the raw file */
    private const SENSITIVE = [
        'contains an e-mail address' => '/[A-Za-z0-9._%+\-]+@[A-Za-z0-9\-]+(?:\.[A-Za-z0-9\-]+)*\.[A-Za-z]{2,}/',
        'contains what looks like an access token' => '/\bbearer\s+[A-Za-z0-9._~+\/=\-]{8,}|\beyJ[A-Za-z0-9_\-]{6,}\.[A-Za-z0-9_\-]{6,}|\b(?:access_token|refresh_token|id_token|client_secret|api_key|token|code)=[^\s&"\']{6,}|(?<![A-Za-z0-9_\-+=])(?=[A-Za-z_\-+=]*\d)[A-Za-z0-9_\-+=]{32,}(?![A-Za-z0-9_\-+=])/i',
        'contains a local address (localhost)' => '/\b(?:localhost|0\.0\.0\.0|127(?:\.\d{1,3}){3})\b|\[::1\]|\b[a-z0-9\-]+\.(?:local|localhost|internal)\b/i',
    ];

    public function __construct(
        #[Autowire(env: 'DEMO_MCP_TRANSCRIPT_FILE')]
        private readonly string $file,
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDirectory,
    ) {
    }

    /** @throws InvalidTranscript */
    public function load(): ?Transcript
    {
        $content = is_file($this->file) ? file_get_contents($this->file) : false;
        if (false === $content || '' === trim($content)) {
            return null;
        }
        self::assertNothingSensitive($content);

        try {
            $data = Yaml::parse($content);
        } catch (ParseException $exception) {
            throw new InvalidTranscript('not valid YAML (' . $exception->getMessage() . ')', $exception->getParsedLine() > 0 ? $exception->getParsedLine() : null);
        }
        if (null === $data) {
            return null;
        }
        if (!\is_array($data)) {
            throw new InvalidTranscript('the file must be a YAML mapping');
        }

        return new Transcript(
            self::date($data['recorded_at'] ?? null),
            self::locale($data['locale'] ?? null),
            self::assistant($data['assistant'] ?? null),
            self::exchanges($data['exchanges'] ?? null),
            $this->media($data['media'] ?? null),
        );
    }

    private static function assertNothingSensitive(string $content): void
    {
        foreach (explode("\n", $content) as $index => $line) {
            if (str_starts_with(ltrim($line), '#')) {
                continue;
            }
            foreach (self::SENSITIVE as $reason => $pattern) {
                if (1 === preg_match($pattern, $line)) {
                    throw new InvalidTranscript($reason, $index + 1);
                }
            }
        }
    }

    private static function date(mixed $value): string
    {
        $date = \is_string($value) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
        if (false === $date || $date->format('Y-m-d') !== $value) {
            throw new InvalidTranscript('"recorded_at" must be a quoted date, YYYY-MM-DD');
        }

        return $value;
    }

    private static function locale(mixed $value): string
    {
        if (!\is_string($value) || !\in_array($value, self::LOCALES, true)) {
            throw new InvalidTranscript('"locale" must be "fr" or "en"');
        }

        return $value;
    }

    private static function assistant(mixed $value): string
    {
        if (!\is_string($value) || '' === trim($value) || mb_strlen($value) > 60) {
            throw new InvalidTranscript('"assistant" must be the name of the assistant (60 characters at most)');
        }

        return trim($value);
    }

    /** @return list<TranscriptExchange> */
    private static function exchanges(mixed $value): array
    {
        if (!\is_array($value) || !array_is_list($value) || \count($value) < 4 || \count($value) > 6) {
            throw new InvalidTranscript('"exchanges" must hold 4 to 6 exchanges');
        }
        $exchanges = [];
        foreach ($value as $index => $exchange) {
            $number = $index + 1;
            $user = \is_array($exchange) ? self::text($exchange['user'] ?? null) : null;
            $assistant = \is_array($exchange) ? self::text($exchange['assistant'] ?? null) : null;
            if (null === $user || null === $assistant) {
                throw new InvalidTranscript(\sprintf('exchange %d needs a "user" message and an "assistant" answer (2000 characters at most)', $number));
            }
            $exchanges[] = new TranscriptExchange($user, $assistant, self::tools($exchange['tools'] ?? [], $number));
        }

        return $exchanges;
    }

    private static function text(mixed $value): ?string
    {
        return \is_string($value) && '' !== trim($value) && mb_strlen($value) <= 2000 ? trim($value) : null;
    }

    /** @return list<string> */
    private static function tools(mixed $value, int $number): array
    {
        $tools = [];
        foreach (\is_array($value) ? $value : [$value] as $tool) {
            if (!\is_string($tool) || 1 !== preg_match('/^[a-z][a-z0-9_]{0,63}$/', $tool)) {
                throw new InvalidTranscript(\sprintf('exchange %d: "tools" must list tool names such as list_my_orders', $number));
            }
            $tools[] = $tool;
        }

        return $tools;
    }

    private function media(mixed $value): ?TranscriptMedia
    {
        if (null === $value) {
            return null;
        }
        if (!\is_array($value)) {
            throw new InvalidTranscript('"media" must be a mapping with "kind", "src" and "description"');
        }
        $kind = $value['kind'] ?? null;
        if (!\in_array($kind, [TranscriptMedia::VIDEO, TranscriptMedia::GIF], true)) {
            throw new InvalidTranscript('"media.kind" must be "video" or "gif"');
        }
        $description = self::text($value['description'] ?? null);
        if (null === $description) {
            throw new InvalidTranscript('"media.description" is required: it is the text alternative of the recording');
        }
        $source = self::mediaPath($value['src'] ?? null, TranscriptMedia::VIDEO === $kind ? '/\.(?:mp4|webm)$/' : '/\.gif$/', 'media.src');
        $poster = isset($value['poster']) ? self::mediaPath($value['poster'], '/\.(?:png|jpe?g|webp)$/', 'media.poster') : null;
        if (!$this->exists($source) || (TranscriptMedia::GIF === $kind && (null === $poster || !$this->exists($poster))) || (null !== $poster && !$this->exists($poster))) {
            return null;
        }

        return new TranscriptMedia($kind, $source, $description, $poster);
    }

    private static function mediaPath(mixed $value, string $extension, string $name): string
    {
        if (!\is_string($value) || 1 !== preg_match('#^' . preg_quote(self::MEDIA_DIRECTORY, '#') . '[A-Za-z0-9._\-]+$#', $value) || 1 !== preg_match($extension, $value)) {
            throw new InvalidTranscript(\sprintf('"%s" must be a file of %s with the right extension', $name, self::MEDIA_DIRECTORY));
        }

        return $value;
    }

    private function exists(string $path): bool
    {
        return is_file(rtrim($this->publicDirectory, '/') . $path);
    }
}
