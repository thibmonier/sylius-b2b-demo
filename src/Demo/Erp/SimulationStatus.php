<?php

declare(strict_types=1);

namespace App\Demo\Erp;

enum SimulationStatus: string
{
    case Acknowledged = 'acknowledged';
    case Rejected = 'rejected';
    case NothingToProcess = 'nothing_to_process';
}
