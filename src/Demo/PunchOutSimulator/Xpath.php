<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/** Typed XPath helpers shared by the cXML readers of the simulator. */
final class Xpath
{
    public static function first(\DOMXPath $xpath, string $query, ?\DOMNode $context = null): ?\DOMNode
    {
        $nodes = $xpath->query($query, $context);

        $node = false === $nodes ? null : $nodes->item(0);

        return $node instanceof \DOMNode ? $node : null;
    }

    /** @return list<\DOMNode> */
    public static function all(\DOMXPath $xpath, string $query): array
    {
        $nodes = $xpath->query($query);

        $found = [];
        foreach (false === $nodes ? [] : $nodes as $node) {
            if ($node instanceof \DOMNode) {
                $found[] = $node;
            }
        }

        return $found;
    }

    public static function text(\DOMXPath $xpath, string $query, ?\DOMNode $context = null): string
    {
        $node = self::first($xpath, $query, $context);

        return $node instanceof \DOMNode ? trim($node->textContent) : '';
    }
}
