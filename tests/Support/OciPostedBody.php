<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Tmonier\SyliusPunchoutCxmlPlugin\Oci\OciFormWriter;
use Tmonier\SyliusPunchoutCxmlPlugin\Transfer\TransferLine;

/** The body the plugin's OCI transfer form posts, as PHP parses it (NEW_ITEM-DESCRIPTION[1] becomes an indexed list). */
final class OciPostedBody
{
    /** @return array<array-key, mixed> */
    public static function of(TransferLine ...$lines): array
    {
        $pairs = (new OciFormWriter())->fields(array_values($lines), ['~OkCode' => 'ADD']);
        parse_str(implode('&', array_map(static fn (array $pair): string => rawurlencode($pair[0]) . '=' . rawurlencode($pair[1]), $pairs)), $fields);

        return $fields;
    }
}
