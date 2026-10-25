<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Sylius\Bundle\MoneyBundle\Formatter\MoneyFormatterInterface;

/** Amounts and dates of the account page in the language of the page ("1 234,50 €", "21 oct. 2026"). */
final readonly class AccountFormatter
{
    public function __construct(private MoneyFormatterInterface $money)
    {
    }

    public function money(int $amount, string $currency, string $locale): string
    {
        return $this->money->format($amount, $currency, $locale);
    }

    public function date(\DateTimeInterface $date, string $locale): string
    {
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE, $date->getTimezone()->getName());

        return (string) $formatter->format($date);
    }
}
