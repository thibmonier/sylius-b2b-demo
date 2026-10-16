<?php

declare(strict_types=1);

namespace App\Tests\Unit\Template;

use App\Entity\Order\Order;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * US-022 — PO reference shown on the admin order page and in the confirmation e-mail:
 * present, absent (no error) and corrupted (escaped).
 */
final class PoReferenceTemplatesTest extends KernelTestCase
{
    private const CORRUPTED = '<script>alert(1)</script>';

    public function testAdminSummaryShowsPoReferenceAndCostCenter(): void
    {
        $html = $this->renderAdmin($this->order('PO-1001', 'CC-42'));

        self::assertStringContainsString('PO-1001', $html);
        self::assertStringContainsString('CC-42', $html);
    }

    public function testAdminSummaryHidesTheRowsWhenEmpty(): void
    {
        $html = $this->renderAdmin($this->order(null, null));

        self::assertStringNotContainsString('admin-order-po-reference', $html);
        self::assertStringNotContainsString('admin-order-cost-center', $html);
    }

    public function testAdminSummaryEscapesACorruptedValue(): void
    {
        $html = $this->renderAdmin($this->order(self::CORRUPTED, null));

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function testEmailShowsThePoReference(): void
    {
        self::assertStringContainsString('PO-1001', $this->renderEmail($this->order('PO-1001', null)));
    }

    public function testEmailHasNoPoLineWhenEmpty(): void
    {
        self::assertStringNotContainsString('email-po-reference', $this->renderEmail($this->order(null, null)));
    }

    public function testEmailEscapesACorruptedValue(): void
    {
        $html = $this->renderEmail($this->order(self::CORRUPTED, null));

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    private function order(?string $poReference, ?string $costCenter): Order
    {
        $order = new Order();
        $order->setPoReference($poReference);
        $order->setCostCenter($costCenter);

        return $order;
    }

    private function renderAdmin(Order $order): string
    {
        return $this->twig()->render('admin/order/po_reference_summary.html.twig', [
            'hookable_metadata' => ['context' => ['resource' => $order]],
        ]);
    }

    private function renderEmail(Order $order): string
    {
        return $this->twig()->render('email/_po_reference.html.twig', ['order' => $order, 'localeCode' => 'fr_FR']);
    }

    private function twig(): Environment
    {
        self::bootKernel();
        $twig = self::getContainer()->get('twig');
        \assert($twig instanceof Environment);

        return $twig;
    }
}
