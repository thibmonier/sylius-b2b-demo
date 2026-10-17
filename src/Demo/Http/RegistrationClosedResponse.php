<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\HttpFoundation\Response;

/** The one answer given wherever a visitor would leave personal data while DEMO_REGISTRATION_OPEN=0. */
final class RegistrationClosedResponse
{
    public static function create(): Response
    {
        return new Response(
            'Registrations are closed on this demo. / Les inscriptions sont fermées sur cette démo.',
            Response::HTTP_FORBIDDEN,
            ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store'],
        );
    }
}
