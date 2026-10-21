<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

final class CurlLinkProbe implements LinkProbe
{
    public function status(string $url): int
    {
        $curl = \curl_init($url);
        if (false === $curl) {
            return 0;
        }
        \curl_setopt_array($curl, [
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_MAXREDIRS => 5,
            \CURLOPT_TIMEOUT => 15,
            \CURLOPT_USERAGENT => 'vitrine-readme link check',
        ]);
        \curl_exec($curl);
        $status = (int) \curl_getinfo($curl, \CURLINFO_RESPONSE_CODE);

        return $status;
    }
}
