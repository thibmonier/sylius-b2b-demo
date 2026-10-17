<?php

declare(strict_types=1);

namespace App\Demo\Webhook;

use Symfony\Component\Validator\Constraint;

/** In the demo mode, the URL of a webhook endpoint is the receiver of the demo and nothing else (SSRF protection). */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class DemoEndpointUrl extends Constraint
{
    public string $message = 'app.demo_webhook.url.receiver_only';

    public function validatedBy(): string
    {
        return DemoEndpointUrlValidator::class;
    }
}
