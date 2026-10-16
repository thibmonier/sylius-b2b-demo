<?php

// Test witness for docker/bench/leak-test.sh, NEVER used in production. Started as the worker script of a throw-away
// container (FRANKENPHP_WORKER_DIRECTIVE="worker /spike/witness-index.php 1", see ADR-0013), it wraps the kernel in a
// decorator that keeps the demo channel cookie of one request for the next one, like a stateful service would. The
// leak test must fail against it, which proves that the test can detect a leak.

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

require_once '/app/vendor/autoload_runtime.php';

final class LeakWitnessKernel implements HttpKernelInterface, TerminableInterface
{
    private ?string $retainedChannel = null;

    public function __construct(private readonly Kernel $kernel)
    {
    }

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        if (!$request->cookies->has('demo_channel') && null !== $this->retainedChannel) {
            $request->cookies->set('demo_channel', $this->retainedChannel);
        }
        $this->retainedChannel = $request->cookies->get('demo_channel');

        return $this->kernel->handle($request, $type, $catch);
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->kernel->terminate($request, $response);
    }
}

return function (array $context) {
    return new LeakWitnessKernel(new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']));
};
