<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/** US-099: the four numbered steps of the simulator page. The visitor is in the shop during the second one, so the page itself only shows the other three. */
final class SimulatorStep
{
    public const LAUNCH = 1;

    public const SHOP = 2;

    public const CART = 3;

    public const ORDER = 4;

    /** @param array<string, mixed> $parameters what the controller hands to the template */
    public static function of(array $parameters): int
    {
        return match (true) {
            isset($parameters['sent']) => self::ORDER,
            isset($parameters['cart']) => self::CART,
            default => self::LAUNCH,
        };
    }
}
