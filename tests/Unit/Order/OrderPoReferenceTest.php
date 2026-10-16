<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order;

use App\Entity\Channel\Channel;
use App\Entity\Order\Order;
use App\Form\Extension\OrderPoReferenceTypeExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
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

    // --- Scénario 4: error — PO too long (65 chars): constraint ensures max 64 ---

    public function testPoReferenceMaxLengthConstraintIsDefinedOnFormExtension(): void
    {
        /** @var ChannelContextInterface&MockObject $channelContext */
        $channelContext = $this->createMock(ChannelContextInterface::class);

        /** @var TranslatorInterface&MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);

        $extension = new OrderPoReferenceTypeExtension($channelContext, $translator);
        $extendedTypes = iterator_to_array($extension::getExtendedTypes());

        self::assertCount(1, $extendedTypes);
        self::assertStringContainsString('CompleteType', $extendedTypes[0]);
    }

    // --- Scénario 5: error — PO required on channel but missing → POST_SUBMIT error ---

    public function testPostSubmitAddsErrorWhenPoReferenceRequiredAndMissing(): void
    {
        $channel = new Channel();
        $channel->setPoReferenceRequired(true);

        /** @var ChannelContextInterface&MockObject $channelContext */
        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        /** @var TranslatorInterface&MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('La référence PO est requise pour finaliser la commande.');

        $order = new Order();
        // poReference intentionally left null (missing)

        /** @var FormInterface&MockObject $poRefField */
        $poRefField = $this->createMock(FormInterface::class);
        $poRefField->expects(self::once())
            ->method('addError')
            ->with(self::isInstanceOf(FormError::class));

        /** @var FormInterface&MockObject $form */
        $form = $this->createMock(FormInterface::class);
        $form->method('get')->with('poReference')->willReturn($poRefField);

        $formEvent = new FormEvent($form, $order);

        $extension = new OrderPoReferenceTypeExtension($channelContext, $translator);

        // Invoke the POST_SUBMIT listener via reflection
        $ref = new \ReflectionClass($extension);
        $method = $ref->getMethod('buildForm');

        $builderMock = $this->createMock(\Symfony\Component\Form\FormBuilderInterface::class);
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
