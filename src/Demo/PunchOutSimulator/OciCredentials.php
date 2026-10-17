<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/** What the simulated SAP system presents at the OCI entry of the shop: the buyer code, the user name and the password. */
final readonly class OciCredentials
{
    public function __construct(
        public string $buyerCode,
        public string $username,
        #[\SensitiveParameter]
        public string $password,
    ) {
    }

    /**
     * The fields of the entry call. The password travels in the POST body, never in a URL (`oci_allow_get` stays off).
     *
     * @return array{USERNAME: string, PASSWORD: string, HOOK_URL: string}
     */
    public function entryFields(string $hookUrl): array
    {
        return ['USERNAME' => $this->username, 'PASSWORD' => $this->password, 'HOOK_URL' => $hookUrl];
    }
}
