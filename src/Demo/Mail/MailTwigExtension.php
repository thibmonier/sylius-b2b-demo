<?php

declare(strict_types=1);

namespace App\Demo\Mail;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/** Masks the addresses the public demo mailbox shows (US-028): "claire@acme.example" becomes "c***@acme.example". */
final class MailTwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [new TwigFilter('app_mask_email', self::maskAddresses(...))];
    }

    public static function maskAddresses(string $text): string
    {
        return preg_replace('/([^\s<>,;"\'@])[^\s<>,;"\'@]*@([^\s<>,;"\']+)/', '$1***@$2', $text) ?? '';
    }
}
