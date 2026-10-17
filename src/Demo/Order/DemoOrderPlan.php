<?php

declare(strict_types=1);

namespace App\Demo\Order;

/**
 * The fixed order history of the demo (US-011): same orders, same lines, same relative dates on every reset.
 */
final class DemoOrderPlan
{
    private const COMPLETION_HOUR = 10;

    /** @return list<DemoOrderSpec> */
    public static function all(): array
    {
        return [
            // Acme Industries: Claire (buyer, 500 EUR limit), Paul (approver)
            new DemoOrderSpec('claire@acme.example', 172, ['EPI-GANT-001' => 24, 'VISS-002' => 2], 'ACME-PO-2026-0412', OrderScenario::Shipped, 'ATELIER-01'),
            new DemoOrderSpec('claire@acme.example', 131, ['EPI-CASK-001' => 30, 'EPI-GANT-001' => 12], 'ACME-PO-2026-0458', OrderScenario::ApprovedShipped, 'ATELIER-01', 'paul@acme.example'),
            new DemoOrderSpec('paul@acme.example', 94, ['OUTI-001' => 6, 'EMBA-001' => 20], 'ACME-PO-2026-0503', OrderScenario::Shipped, 'MAINT-02'),
            new DemoOrderSpec('claire@acme.example', 61, ['VISS-002' => 5], 'ACME-PO-2026-0547', OrderScenario::Cancelled, 'ATELIER-01'),
            new DemoOrderSpec('paul@acme.example', 33, ['EPI-GANT-001' => 36], 'ACME-PO-2026-0591', OrderScenario::Paid, 'MAINT-02'),
            new DemoOrderSpec('claire@acme.example', 4, ['EPI-CASK-001' => 40, 'OUTI-001' => 10], 'ACME-PO-2026-0634', OrderScenario::PendingApproval, 'ATELIER-01'),
            // TechParts SAS: Marc
            new DemoOrderSpec('marc@techparts.example', 150, ['VISS-001' => 30], 'TP-2026-117', OrderScenario::Shipped),
            new DemoOrderSpec('marc@techparts.example', 78, ['EPI-GANT-001' => 10, 'EMBA-001' => 8], 'TP-2026-142', OrderScenario::Paid),
            new DemoOrderSpec('marc@techparts.example', 19, ['OUTI-001' => 4], 'TP-2026-163', OrderScenario::Cancelled),
            // GlobalMaint SA: Henri
            new DemoOrderSpec('henri@globalmaint.example', 110, ['EPI-CASK-001' => 15], 'GM-8841', OrderScenario::Shipped, 'SITE-LYON'),
            new DemoOrderSpec('henri@globalmaint.example', 52, ['VISS-002' => 12, 'EMBA-001' => 10], 'GM-8903', OrderScenario::Refunded, 'SITE-LYON'),
            new DemoOrderSpec('henri@globalmaint.example', 9, ['EPI-GANT-001' => 20], 'GM-8957', OrderScenario::AwaitingPayment, 'SITE-LYON'),
        ];
    }

    public static function completedAt(\DateTimeImmutable $now, int $daysAgo): \DateTimeImmutable
    {
        return $now->modify(sprintf('-%d days', $daysAgo))->setTime(self::COMPLETION_HOUR, 0);
    }
}
