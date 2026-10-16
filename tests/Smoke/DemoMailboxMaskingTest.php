<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Mail\MailpitMessage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/** US-028 — the public mailbox does not display the address of the recipient, in the list nor in the detail. */
final class DemoMailboxMaskingTest extends KernelTestCase
{
    public function testTheListMasksTheRecipient(): void
    {
        $html = $this->twig()->render('demo/mails.html.twig', ['unavailable' => false, 'messages' => [$this->message()]]);

        self::assertStringContainsString('c***@acme.example', $html);
        self::assertStringNotContainsString('claire@acme.example', $html);
    }

    public function testTheDetailMasksTheRecipient(): void
    {
        $html = $this->twig()->render('demo/mail.html.twig', ['message' => $this->message()]);

        self::assertStringContainsString('c***@acme.example', $html);
        self::assertStringNotContainsString('claire@acme.example', $html);
    }

    private function message(): MailpitMessage
    {
        return new MailpitMessage('abc', 'Votre inscription a été approuvée', 'no-reply@fournitures-pro.example', 'Claire Dupont <claire@acme.example>', new \DateTimeImmutable('2026-10-16 10:00'), 'Bonjour', '');
    }

    private function twig(): Environment
    {
        self::bootKernel();
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        return $twig;
    }
}
