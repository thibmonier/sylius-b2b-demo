<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mail;

use App\Demo\Mail\MailpitMessage;
use App\Demo\Mail\SensitiveMailFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-028: the deny-list that keeps secrets out of the public demo mailbox. */
final class SensitiveMailFilterTest extends TestCase
{
    #[DataProvider('sensitive')]
    public function testSensitiveMailsAreDetected(string $subject, string $text, string $html = ''): void
    {
        self::assertTrue((new SensitiveMailFilter())->isSensitive($this->mail($subject, $text, $html)));
    }

    #[DataProvider('harmless')]
    public function testOrdinaryMailsAreKept(string $subject, string $text, string $html = ''): void
    {
        self::assertFalse((new SensitiveMailFilter())->isSensitive($this->mail($subject, $text, $html)));
    }

    /** @return iterable<string, array{string, string, 2?: string}> */
    public static function sensitive(): iterable
    {
        yield 'EN reset subject' => ['Reset your password', 'Hello'];
        yield 'FR reset subject' => ['Réinitialisation de votre mot de passe', 'Bonjour'];
        yield 'FR reset accent-less' => ['Reinitialiser mon mot de passe', 'Bonjour'];
        yield 'verification subject' => ['Please verify your account', 'Hello'];
        yield 'FR verification' => ['Vérification de votre compte', 'Bonjour'];
        yield 'confirm email' => ['Confirm your e-mail address', 'Hello'];
        yield 'invitation' => ['Invitation à rejoindre Acme', 'Bonjour'];
        yield 'magic link' => ['Your magic link', 'x'];
        yield 'sylius reset url' => ['Notification', 'Go to http://shop.test/en_US/account/reset-password/aB3dE5gH7jK9mN1pQ3sT'];
        yield 'sylius verify url' => ['Welcome', 'http://shop.test/fr_FR/account/verify/abc'];
        yield 'token query param' => ['Hello', 'http://shop.test/go?token=1'];
        yield 'compound query param' => ['Hello', 'http://shop.test/go?a=1&reset_hash=1'];
        yield 'opaque mixed-case segment' => ['Your order', 'http://shop.test/order/Aa1Bb2Cc3Dd4Ee5Ff6Gg7'];
        yield 'hex segment' => ['Your order', 'http://shop.test/order/0123456789abcdef0123456789abcdef'];
        yield 'url only in html, entity-encoded' => ['Hello', '', '<a href="http://shop.test/go?x=1&amp;token=abc">go</a>'];
        yield 'relative link in html' => ['Hello', '', '<a href="/en_US/account/reset-password/abc">go</a>'];
    }

    /** @return iterable<string, array{string, string, 2?: string}> */
    public static function harmless(): iterable
    {
        yield 'order confirmation' => ['Commande #000001 confirmée', 'Merci pour votre commande. http://shop.test/fr_FR/products/stylo-bille-bleu'];
        yield 'quote notification' => ['New quote request', 'See http://shop.test/admin/quotes/12'];
        yield 'plain text' => ['Hello', 'No link at all'];
        yield 'invoice' => ['Facture FA-2026-0001', 'Total 120 EUR'];
        yield 'harmless html' => ['Update', '', '<p>Hi <a href="https://example.com/orders/000001">order</a></p>'];
    }

    private function mail(string $subject, string $text, string $html): MailpitMessage
    {
        return new MailpitMessage('id1', $subject, 'a@b.example', 'c@d.example', null, $text, $html);
    }
}
