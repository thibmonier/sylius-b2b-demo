<?php

declare(strict_types=1);

namespace App\Demo\Mail;

/** A mail read from Mailpit, kept as plain strings: it is only ever displayed. */
final readonly class MailpitMessage
{
    public function __construct(
        public string $id,
        public string $subject,
        public string $from,
        public string $to,
        public ?\DateTimeImmutable $date,
        public string $text,
        public string $html,
        /** Cc and Bcc addresses: they count as recipients for the allow-list. */
        public string $copies = '',
    ) {
    }

    /** What the demo shows: the text part, or the HTML stripped of every tag. Never raw HTML. */
    public function displayText(): string
    {
        if ('' !== trim($this->text)) {
            return $this->text;
        }

        $text = preg_replace('#<(script|style)\b.*?</\1>#is', '', $this->html) ?? '';

        return trim(html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
    }
}
