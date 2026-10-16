<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/**
 * The cXML 1.2.071 skeleton shared by the messages the simulator sends: header (From, To, Sender with
 * the shared secret) and the Request element. Same shape as the plugin test fixtures.
 */
final class CxmlEnvelope
{
    private const DOCTYPE = '<!DOCTYPE cXML PUBLIC "-//cXML.org//DTD cXML 1.2//EN" "http://xml.cxml.org/schemas/cXML/1.2.071/cXML.dtd">';

    private const SUPPLIER_DOMAIN = 'NetworkId';

    private const SUPPLIER_IDENTITY = 'supplier';

    public readonly \DOMDocument $document;

    public readonly \DOMElement $request;

    public function __construct(BuyerCredentials $credentials, string $payloadId, \DateTimeImmutable $now)
    {
        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;

        $cxml = $this->append($this->document, 'cXML', null, [
            'version' => '1.2.071',
            'payloadID' => $payloadId,
            'timestamp' => $now->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP'),
        ]);
        $header = $this->append($cxml, 'Header');
        $this->credential($this->append($header, 'From'), $credentials->fromDomain, $credentials->fromIdentity);
        $this->credential($this->append($header, 'To'), self::SUPPLIER_DOMAIN, self::SUPPLIER_IDENTITY);
        $sender = $this->append($header, 'Sender');
        $credential = $this->credential($sender, $credentials->senderDomain, $credentials->senderIdentity);
        $this->append($credential, 'SharedSecret', $credentials->sharedSecret);
        $this->append($sender, 'UserAgent', 'Acme e-procurement simulator (demo)');

        $this->request = $this->append($cxml, 'Request', null, ['deploymentMode' => 'production']);
    }

    /** @param array<string, string> $attributes */
    public function append(\DOMNode $parent, string $name, ?string $text = null, array $attributes = []): \DOMElement
    {
        $document = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        \assert($document instanceof \DOMDocument);
        $element = $document->createElement($name);
        if (null !== $text) {
            $element->appendChild($document->createTextNode($text));
        }
        foreach ($attributes as $attribute => $value) {
            $element->setAttribute($attribute, $value);
        }
        $parent->appendChild($element);

        return $element;
    }

    public function toXml(): string
    {
        $xml = (string) $this->document->saveXML();
        $newline = (int) strpos($xml, "\n");

        return substr($xml, 0, $newline) . "\n" . self::DOCTYPE . substr($xml, $newline);
    }

    private function credential(\DOMElement $parent, string $domain, string $identity): \DOMElement
    {
        $credential = $this->append($parent, 'Credential', null, ['domain' => $domain]);
        $this->append($credential, 'Identity', $identity);

        return $credential;
    }
}
