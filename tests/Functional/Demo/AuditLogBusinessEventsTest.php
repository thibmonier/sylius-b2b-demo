<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-088: against a database loaded with the `demo` suite (what app:demo:reset produces), the audit log holds no
 * line of the reset itself, only a few dated business events attributed to demo accounts.
 */
final class AuditLogBusinessEventsTest extends WebTestCase
{
    use DatabaseAccess;

    public function testTheLogHoldsNoCreationLineOfTheReset(): void
    {
        self::bootKernel();

        $creations = $this->intValue("SELECT COUNT(*) FROM tmonier_audit_log_entry WHERE action = 'create'");
        self::assertSame(0, $creations, sprintf('The audit log must hold no "create" line of the reset, found %d.', $creations));
    }

    public function testFiveToSixBusinessEventsAreDatedAndAttributedToDemoAccounts(): void
    {
        self::bootKernel();

        $rows = $this->db()->fetchAllAssociative('SELECT actor_name, actor_type, created_at, resource_type, changes FROM tmonier_audit_log_entry');
        self::assertGreaterThanOrEqual(5, \count($rows));
        self::assertLessThanOrEqual(6, \count($rows));
        foreach ($rows as $row) {
            self::assertNotEmpty($row['actor_name']);
            self::assertNotEmpty($row['created_at']);
            self::assertContains($row['actor_type'], ['admin', 'customer']);
        }
    }

    public function testTheEventsCoverPriceRegistrationCeilingAndApproval(): void
    {
        self::bootKernel();

        $types = $this->db()->fetchFirstColumn('SELECT DISTINCT resource_type FROM tmonier_audit_log_entry');
        foreach (['channel_pricing', 'member_limit', 'order_approval'] as $type) {
            self::assertContains($type, $types);
        }
        self::assertSame(1, $this->intValue("SELECT COUNT(*) FROM tmonier_audit_log_entry WHERE resource_type = 'customer' AND changes LIKE '%approvalStatus%'"));
    }

    public function testEveryEventMatchesTheDataInTheDatabase(): void
    {
        self::bootKernel();

        self::assertSame(50_000, $this->intValue("SELECT ml.order_limit FROM tmonier_audit_log_entry e JOIN tmonier_purchase_approval_member_limit ml ON ml.id = e.resource_id WHERE e.resource_type = 'member_limit'"));
        self::assertSame('approved', $this->stringValue("SELECT oa.state FROM tmonier_audit_log_entry e JOIN tmonier_purchase_approval_order_approval oa ON oa.id = e.resource_id WHERE e.resource_type = 'order_approval'"));
    }
}
