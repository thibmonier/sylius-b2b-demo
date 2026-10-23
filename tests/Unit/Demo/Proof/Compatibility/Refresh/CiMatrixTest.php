<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Compatibility\Refresh\CiMatrix;
use PHPUnit\Framework\TestCase;

/** US-171: the versions a plugin's CI really runs, read from the matrix of its workflow (base axes, exclude, include). */
final class CiMatrixTest extends TestCase
{
    public function testTheAxesAreTheValuesOfTheCombinationsThatRun(): void
    {
        $axes = CiMatrix::axes(<<<'YAML'
            jobs:
                tests:
                    strategy:
                        matrix:
                            php: ["8.3", "8.4"]
                            symfony: ["^7.4"]
                            sylius: ["~2.2.0", "~2.3.0"]
                            database: ["mysql:8.4"]
                            node: ["22.x"]
                            exclude:
                                - sylius: "~2.3.0"
                                  php: "8.2"
                                - sylius: "~2.2.0"
                                  symfony: "^8.0"
                            include:
                                - php: "8.2"
                                  symfony: "^6.4"
                                  sylius: "~2.2.0"
                                  database: "mysql:8.4"
                                - php: "8.4"
                                  symfony: "^8.0"
                                  sylius: "~2.3.0"
                                  database: "mysql:8.4"
                                - php: "8.4"
                                  symfony: "^7.4"
                                  sylius: "~2.3.0"
                                  database: "postgres:17"
                                - php: "8.4"
                                  symfony: "^7.4"
                                  sylius: "~2.2.0"
                                  database: "mariadb:11.4"
            YAML);

        self::assertSame(['php' => ['8.2', '8.3', '8.4'], 'sylius' => ['2.2', '2.3'], 'symfony' => ['6.4', '7.4', '8.0'], 'database' => ['mariadb:11.4', 'mysql:8.4', 'postgres:17']], $axes);
    }

    public function testAValueOnlyUsedByExcludedCombinationsIsNotReported(): void
    {
        $axes = CiMatrix::axes(<<<'YAML'
            jobs:
                tests:
                    strategy:
                        matrix:
                            php: ["8.3"]
                            symfony: ["^7.4", "^8.0"]
                            sylius: ["~2.2.0"]
                            database: ["mysql:8.4"]
                            exclude:
                                - sylius: "~2.2.0"
                                  symfony: "^8.0"
            YAML);

        self::assertSame(['7.4'], $axes['symfony'] ?? null);
    }

    public function testTheMatricesOfSeveralJobsAreMerged(): void
    {
        $axes = CiMatrix::axes(<<<'YAML'
            jobs:
                a:
                    strategy:
                        matrix: { php: ["8.3"], sylius: ["~2.2.0"], symfony: ["^7.4"], database: ["mysql:8.4"] }
                b:
                    strategy:
                        matrix: { php: ["8.4"], sylius: ["~2.3.0"], symfony: ["^8.0"], database: ["mysql:8.4"] }
            YAML);

        self::assertSame(['8.3', '8.4'], $axes['php'] ?? null);
        self::assertSame(['2.2', '2.3'], $axes['sylius'] ?? null);
    }

    public function testAnAxisTheWorkflowDoesNotDeclareIsEmptyNotInvented(): void
    {
        $axes = CiMatrix::axes("jobs:\n    t:\n        strategy:\n            matrix:\n                php: [\"8.3\"]\n");

        self::assertSame(['php' => ['8.3'], 'sylius' => [], 'symfony' => [], 'database' => []], $axes);
    }

    public function testNoMatrixOrAnUnreadableWorkflowGivesNothing(): void
    {
        self::assertNull(CiMatrix::axes("jobs:\n    t:\n        steps:\n            - run: true\n"));
        self::assertNull(CiMatrix::axes("jobs: [unclosed\n"));
    }
}
