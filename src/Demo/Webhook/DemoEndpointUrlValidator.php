<?php

declare(strict_types=1);

namespace App\Demo\Webhook;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class DemoEndpointUrlValidator extends ConstraintValidator
{
    public function __construct(
        private readonly DemoWebhookTarget $target,
        #[Autowire(param: 'demo.mode')]
        private readonly bool $demoMode,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DemoEndpointUrl) {
            throw new UnexpectedTypeException($constraint, DemoEndpointUrl::class);
        }
        if (!$this->demoMode || null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value) || !$this->target->accepts($value)) {
            $this->context->buildViolation($constraint->message)->setTranslationDomain('validators')->addViolation();
        }
    }
}
