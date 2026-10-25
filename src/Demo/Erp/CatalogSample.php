<?php

declare(strict_types=1);

namespace App\Demo\Erp;

/** A few variants of the real catalogue of the demo, with the values they have now: what "ERP Démo (fictif)" puts in its files. */
interface CatalogSample
{
    /** @return list<SamplePrice> */
    public function prices(int $limit): array;

    /** @return list<SampleStock> */
    public function stock(int $limit): array;
}
