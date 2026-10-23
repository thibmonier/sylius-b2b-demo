<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order;

use App\Entity\Channel\Channel;
use App\Entity\Order\Order;
use App\Form\Extension\OrderPoReferenceTypeExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * US-021 — Unit tests covering the 5 Gherkin scenarios.
 *
 * Scénario 1 (nominal): PO + cost center saved when both required on channel.
 * Scénario 2 (alt):     PO is optional on channel → no error when left blank.
 * Scénario 3 (alt):     Cost center alone filled + PO optional → OK.
 * Scénario 4 (error):   PO too long (65 chars) — validated by Symfony constraints.
 * Scénario 5 (error):   PO required on channel but missing → validation error.
 */
final class OrderPoReferenceTest extends TestCase
{
    // --- Scénario 1: nominal — PO + cost center saved on required channel ---

    public function testPoReferenceAndCostCenterAreSavedOnOrder(): void
    {
        $order = new Order();
        $order->setPoReference('PO-2026-001');
        $order->setCostCenter('COST-IT');

        self::assertSame('PO-2026-001', $order->getPoReference());
        self::assertSame('COST-IT', $order->getCostCenter());
    }

    // --- Scénario 2: alternative — PO not required on channel, left blank → no error ---

    public function testPoReferenceIsNullableWhenNotRequired(): void
    {
        $order = new Order();
        $order->setPoReference(null);
        $order->setCostCenter(null);

        self::assertNull($order->getPoReference());
        self::assertNull($order->getCostCenter());
    }

    // --- Scénario 3: alternative — only cost center filled, PO optional → OK ---

    public function testCostCenterIsSavedWithoutPoReference(): void
    {
        $order = new Order();
        $order->setPoReference(null);
        $order->setCostCenter('COST-FIN');

        self::assertNull($order->getPoReference());
        self::assertSame('COST-FIN', $order->getCostCenter());
    }

    // --- Scénario 4: error — real validation, checkout "complete" groups ---

    #[DataProvider('poReferenceProvider')]
    public function testPoReferenceValidationRunsInTheCheckoutCompleteGroups(string $value, bool $valid): void
    {
        $violations = $this->validate('poReference', $value);

        self::assertSame($valid, 0 === \count($violations));
    }

    /** @return iterable<string, array{string, bool}> */
    public static function poReferenceProvider(): iterable
    {
        yield 'valid' => ['PO-2026-001', true];
        yield 'max length' => [str_repeat('A', 64), true];
        yield 'too long' => [str_repeat('A', 65), false];
        yield 'html' => ['<b>PO</b>', false];
        yield 'control char' => ["PO\x01", false];
        yield 'trailing newline' => ["PO-1\n", false];
    }

    #[DataProvider('costCenterProvider')]
    public function testCostCenterValidationRunsInTheCheckoutCompleteGroups(string $value, bool $valid): void
    {
        self::assertSame($valid, 0 === \count($this->validate('costCenter', $value)));
    }

    /** @return iterable<string, array{string, bool}> */
    public static function costCenterProvider(): iterable
    {
        yield 'valid' => ['COST IT_1.2-b', true];
        yield 'invalid chars' => ['<script>', false];
        yield 'too long' => [str_repeat('A', 51), false];
    }

    public function testCostCenterFieldIsOnlyAddedWhenEnabledOnTheChannel(): void
    {
        self::assertSame(['poReference'], $this->addedFields(false));
        self::assertSame(['poReference', 'costCenter'], $this->addedFields(true));
    }

    // --- Scénario 5: error — PO required on channel but missing → POST_SUBMIT error ---

    public function testPostSubmitAddsErrorWhenPoReferenceRequiredAndMissing(): void
    {
        $channel = new Channel();
        $channel->setPoReferenceRequired(true);

        $channelContext = $this->createStub(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturn('La référence PO est requise pour finaliser la commande.');

        $order = new Order();
        // poReference intentionally left null (missing)

        /** @var FormInterface&MockObject $poRefField */
        $poRefField = $this->createMock(FormInterface::class);
        $poRefField->expects(self::once())
            ->method('addError')
            ->with(self::isInstanceOf(FormError::class));

        $form = $this->createStub(FormInterface::class);
        $form->method('get')->willReturnMap([['poReference', $poRefField]]);

        $formEvent = new FormEvent($form, $order);

        $extension = new OrderPoReferenceTypeExtension($channelContext, $translator);

        // Invoke the POST_SUBMIT listener via reflection
        $ref = new \ReflectionClass($extension);
        $method = $ref->getMethod('buildForm');

        $builderMock = $this->createStub(\Symfony\Component\Form\FormBuilderInterface::class);
        $listenerCaptures = [];

        $builderMock->method('add')->willReturn($builderMock);
        $builderMock->method('addEventListener')->willReturnCallback(
            function (string $event, callable $listener) use (&$listenerCaptures, $builderMock): \Symfony\Component\Form\FormBuilderInterface {
                $listenerCaptures[$event] = $listener;

                return $builderMock;
            },
        );

        $method->invoke($extension, $builderMock, []);

        self::assertArrayHasKey(\Symfony\Component\Form\FormEvents::POST_SUBMIT, $listenerCaptures);
        $listenerCaptures[\Symfony\Component\Form\FormEvents::POST_SUBMIT]($formEvent);
    }

    /** @return list<string> */
    private function addedFields(bool $costCenterEnabled): array
    {
        $channel = new Channel();
        $channel->setCostCenterEnabled($costCenterEnabled);
        $channelContext = $this->createStub(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        $fields = [];
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('add')->willReturnCallback(function (string $name) use (&$fields, $builder): FormBuilderInterface {
            $fields[] = $name;

            return $builder;
        });
        $builder->method('addEventListener')->willReturn($builder);

        (new OrderPoReferenceTypeExtension($channelContext, $this->createStub(TranslatorInterface::class)))->buildForm($builder, []);

        return $fields;
    }

    private function validate(string $field, string $value): ConstraintViolationListInterface
    {
        $fields = [];
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('add')->willReturnCallback(function (string $name, ?string $type = null, array $options = []) use (&$fields, $builder): FormBuilderInterface {
            $fields[$name] = $options['constraints'];

            return $builder;
        });
        $builder->method('addEventListener')->willReturn($builder);

        $channel = new Channel();
        $channel->setCostCenterEnabled(true);
        $channelContext = $this->createStub(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);
        (new OrderPoReferenceTypeExtension($channelContext, $this->createStub(TranslatorInterface::class)))->buildForm($builder, []);

        return Validation::createValidator()->validate($value, $fields[$field], ['sylius', 'sylius_checkout_complete']);
    }

    // --- Channel configuration tests ---

    public function testChannelPoReferenceRequiredDefaultsFalse(): void
    {
        $channel = new Channel();
        self::assertFalse($channel->isPoReferenceRequired());
        self::assertFalse($channel->isCostCenterEnabled());
    }

    public function testChannelPoReferenceRequiredCanBeEnabled(): void
    {
        $channel = new Channel();
        $channel->setPoReferenceRequired(true);
        $channel->setCostCenterEnabled(true);

        self::assertTrue($channel->isPoReferenceRequired());
        self::assertTrue($channel->isCostCenterEnabled());
    }
}
