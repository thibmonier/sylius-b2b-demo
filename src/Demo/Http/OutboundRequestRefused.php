<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/** Raised instead of any connection to a host outside the demo (a transport failure for the callers). */
final class OutboundRequestRefused extends \RuntimeException implements TransportExceptionInterface
{
}
