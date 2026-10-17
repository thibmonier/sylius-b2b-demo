<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;
use Tmonier\SyliusPunchoutCxmlPlugin\Security\ReturnUrlValidator;

/**
 * The HOOK_URL of an OCI entry. It is where the shop's browser form posts the cart, so it must be one of the return
 * domains of the buyer (HTTPS, no credentials, no private address: the rules of the plugin itself, checked here first so
 * that a refused URL never reaches the shop). The signed state travels in its query: the shop keeps the query and posts
 * the cart back to it.
 */
final readonly class OciHookUrl
{
    public function __construct(private ReturnUrlValidator $validator)
    {
    }

    /** @throws SimulatorException when the URL is not one of the return domains of the buyer */
    public function withState(string $url, string $state, Buyer $buyer): string
    {
        if (!$this->validator->isValid($url, $buyer) || str_contains($url, '#')) {
            throw new SimulatorException('hook_url_not_allowed');
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query(['state' => $state]);
    }
}
