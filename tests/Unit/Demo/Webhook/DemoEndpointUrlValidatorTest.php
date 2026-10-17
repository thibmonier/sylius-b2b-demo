<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Webhook;

use App\Demo\Webhook\DemoEndpointUrl;
use App\Demo\Webhook\DemoEndpointUrlValidator;
use App\Demo\Webhook\DemoWebhookTarget;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * US-029 / T-029-03: the admin side of the SSRF protection.
 *
 * @extends ConstraintValidatorTestCase<DemoEndpointUrlValidator>
 */
final class DemoEndpointUrlValidatorTest extends ConstraintValidatorTestCase
{
    private bool $demoMode = true;

    protected function createValidator(): DemoEndpointUrlValidator
    {
        return new DemoEndpointUrlValidator(new DemoWebhookTarget(8080), $this->demoMode);
    }

    public function testTheReceiverIsAccepted(): void
    {
        $this->validator->validate('http://127.0.0.1:8080/demo/webhooks/receive', new DemoEndpointUrl());

        $this->assertNoViolation();
    }

    public function testAnExternalUrlIsRefused(): void
    {
        $constraint = new DemoEndpointUrl();

        $this->validator->validate('https://hooks.partner.example/webhook', $constraint);

        $this->buildViolation($constraint->message)->assertRaised();
    }

    public function testAnEmptyValueIsLeftToTheNotBlankConstraint(): void
    {
        $this->validator->validate('', new DemoEndpointUrl());

        $this->assertNoViolation();
    }

    public function testOutsideTheDemoModeTheConstraintDoesNothing(): void
    {
        $this->demoMode = false;
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);

        $this->validator->validate('https://hooks.partner.example/webhook', new DemoEndpointUrl());

        $this->assertNoViolation();
    }
}
