<?php

declare(strict_types=1);

namespace Tmonier\SyliusPredictiveAnalyticsPlugin\Calculator;

use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;

/**
 * PHPStan stub for PredictiveReorderCalculatorInterface (plugin not installed locally due to disk constraints).
 */
interface PredictiveReorderCalculatorInterface
{
    public function recalculateForCompany(CompanyInterface $company): int;
}
