<?php

declare(strict_types=1);

namespace App\Demo\Order;

/**
 * The fixed order history of the demo (US-011): same orders, same lines, same relative dates on every reset.
 */
final class DemoOrderPlan
{
    private const COMPLETION_HOUR = 10;

    /** Payment method paid on the credit account of the company (net 30): the order stays an open item until paid. */
    public const ON_ACCOUNT = 'on_account';

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
            // Acme open items (US-097): one overdue (placed 48 days ago, due 18 days ago), one running, paid on account by
            // the approver and the company admin (the buyer limit would turn them into approvals).
            new DemoOrderSpec('paul@acme.example', 48, ['EPI-CHAU-001' => 40], 'ACME-PO-2026-0568', OrderScenario::AwaitingPayment, 'MAINT-02', null, self::ON_ACCOUNT),
            new DemoOrderSpec('isabelle@acme.example', 11, ['EPI-CASK-001' => 120, 'EPI-GANT-001' => 100], 'ACME-PO-2026-0620', OrderScenario::AwaitingPayment, 'ATELIER-01', null, self::ON_ACCOUNT),
            // TechParts SAS: Marc
            new DemoOrderSpec('marc@techparts.example', 150, ['VISS-001' => 30], 'TP-2026-117', OrderScenario::Shipped),
            new DemoOrderSpec('marc@techparts.example', 78, ['EPI-GANT-001' => 10, 'EMBA-001' => 8], 'TP-2026-142', OrderScenario::Paid),
            new DemoOrderSpec('marc@techparts.example', 19, ['OUTI-001' => 4], 'TP-2026-163', OrderScenario::Cancelled),
            // BuildCo SA: Antoine (approver). An open item above the credit limit, tolerated by the account (US-097).
            new DemoOrderSpec('antoine@buildco.example', 7, ['EPI-CHAU-001' => 60], 'BC-2026-031', OrderScenario::AwaitingPayment, null, null, self::ON_ACCOUNT),
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
