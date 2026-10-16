<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\Exception\XmlLoadException;
use Tmonier\SyliusPunchoutCxmlPlugin\Cxml\SafeXmlLoader;

/**
 * Reads the PunchOutOrderMessage the shop posts back to the simulator (form field "cXML-base64"). The
 * XML is loaded with the plugin's defensive loader (no entities, no network).
 */
final readonly class OrderMessageParser
{
    public function __construct(private SafeXmlLoader $loader)
    {
    }

    /** @throws SimulatorException when the document is not a PunchOutOrderMessage */
    public function parseBase64(string $encoded): ReceivedCart
    {
        $xml = base64_decode($encoded, true);
        if (false === $xml || '' === $xml) {
            throw new SimulatorException('invalid_message');
        }

        return $this->parse($xml);
    }

    /** @throws SimulatorException */
    public function parse(string $xml): ReceivedCart
    {
        try {
            $document = $this->loader->load($xml);
        } catch (XmlLoadException) {
            throw new SimulatorException('invalid_message');
        }
        $xpath = new \DOMXPath($document);
        $cookie = Xpath::text($xpath, '/cXML/Message/PunchOutOrderMessage/BuyerCookie');
        $money = Xpath::first($xpath, '/cXML/Message/PunchOutOrderMessage/PunchOutOrderMessageHeader/Total/Money');
        if ('' === $cookie || !$money instanceof \DOMElement) {
            throw new SimulatorException('invalid_message');
        }

        $lines = [];
        foreach (Xpath::all($xpath, '/cXML/Message/PunchOutOrderMessage/ItemIn') as $item) {
            $lines[] = $this->line($xpath, $item);
        }

        return new ReceivedCart($cookie, $money->getAttribute('currency'), trim($money->textContent), $lines);
    }

    private function line(\DOMXPath $xpath, \DOMNode $item): ReceivedLine
    {
        $price = Xpath::first($xpath, 'ItemDetail/UnitPrice/Money', $item);
        $quantity = $item instanceof \DOMElement ? $item->getAttribute('quantity') : '';
        if (!$price instanceof \DOMElement || 1 !== preg_match('/^\d{1,4}$/', $quantity)) {
            throw new SimulatorException('invalid_message');
        }

        return new ReceivedLine(
            Xpath::text($xpath, 'ItemID/SupplierPartID', $item),
            Xpath::text($xpath, 'ItemID/SupplierPartAuxiliaryID', $item),
            Xpath::text($xpath, 'ItemDetail/Description', $item),
            (int) $quantity,
            trim($price->textContent),
            $price->getAttribute('currency'),
            Xpath::text($xpath, 'ItemDetail/UnitOfMeasure', $item) ?: 'EA',
        );
    }
}
