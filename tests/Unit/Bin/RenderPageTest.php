<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\TestCase;

/** T-TECH-37-04: bin/render-page respects TEST_DB_NAME and refuses sylius_b2b_demo like bin/run-tests does. */
final class RenderPageTest extends TestCase
{
    public function testRejectsDevelopmentDatabase(): void
    {
        $output = [];
        $exitCode = 0;
        exec(
            'cd ' . escapeshellarg(__DIR__ . '/../../..') . ' && ' .
            'WORKER_ID=test TEST_DB_NAME=sylius_b2b_demo bin/render-page --out=/tmp/test-render /fr_FR/ 2>&1',
            $output,
            $exitCode
        );

        $stderr = implode("\n", $output);

        self::assertSame(1, $exitCode, 'bin/render-page should refuse TEST_DB_NAME=sylius_b2b_demo');
        self::assertStringContainsString('sylius_b2b_demo', $stderr, 'Error message should mention the refused database name');
    }

    public function testUsesTestDbNameInDatabaseUrl(): void
    {
        // This test verifies that TEST_DB_NAME is used to construct DATABASE_URL
        // by checking that a non-existent database name causes a connection error
        // (not a 500 from using the wrong database)
        $output = [];
        $exitCode = 0;
        exec(
            'cd ' . escapeshellarg(__DIR__ . '/../../..') . ' && ' .
            'APP_ENV=test WORKER_ID=test TEST_DB_NAME=nonexistent_db_xyz bin/render-page --out=/tmp/test-render /fr_FR/ 2>&1',
            $output,
            $exitCode
        );

        $stderr = implode("\n", $output);

        // Should fail trying to connect to nonexistent_db_xyz, not sylius_b2b_demo_test
        self::assertStringContainsString('nonexistent_db_xyz', $stderr, 'Should attempt to use TEST_DB_NAME in DATABASE_URL');
    }
}
