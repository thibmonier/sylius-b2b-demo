<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * US-050: robots.txt depends on the mode, so it is an answer of the application, not a file in public/ (Caddy would
 * serve the file before PHP). In the demo mode the whole site is disallowed (the demo is not promoted; the
 * `X-Robots-Tag` header of NoIndexSubscriber covers the crawlers that read it anyway). Outside the demo mode nothing is
 * disallowed. In the private mode the gate answers first, so only the holder of the password sees this.
 */
final readonly class RobotsController
{
    public function __construct(
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    #[Route('/robots.txt', name: 'app_demo_robots', methods: ['GET'])]
    public function __invoke(): Response
    {
        $rule = $this->demoMode ? 'Disallow: /' : 'Disallow:';

        return new Response("User-agent: *\n" . $rule . "\n", Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
