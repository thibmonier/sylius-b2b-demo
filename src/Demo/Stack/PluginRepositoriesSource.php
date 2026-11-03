<?php

declare(strict_types=1);

namespace App\Demo\Stack;

interface PluginRepositoriesSource
{
    /** @return list<PluginRepositoryStatus> */
    public function statuses(): array;
}
