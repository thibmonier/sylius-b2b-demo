<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\Exception\XmlLoadException;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\SafeXmlLoader;

/** Reads the cXML responses of the shop endpoint: the status, the StartPage URL, the order number. */
final readonly class ResponseParser
{
    public function __construct(private SafeXmlLoader $loader)
    {
    }

    public function parse(string $xml): CxmlResponse
    {
        try {
            $document = $this->loader->load($xml);
        } catch (XmlLoadException) {
            throw new SimulatorException('invalid_response');
        }
        $xpath = new \DOMXPath($document);
        $status = Xpath::first($xpath, '/cXML/Response/Status');
        if (!$status instanceof \DOMElement || 1 !== preg_match('/^\d{3}$/', $status->getAttribute('code'))) {
            throw new SimulatorException('invalid_response');
        }
        $url = Xpath::first($xpath, '/cXML/Response/PunchOutSetupResponse/StartPage/URL');

        return new CxmlResponse(
            (int) $status->getAttribute('code'),
            $status->getAttribute('text'),
            trim($status->textContent),
            $url instanceof \DOMNode ? trim($url->textContent) : null,
        );
    }
}
