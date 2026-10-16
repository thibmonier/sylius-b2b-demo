<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Entity\Channel\Channel;
use App\Order\OrderPoReferenceAwareInterface;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

final class OrderPoReferenceTypeExtension extends AbstractTypeExtension
{
    /** Groups used by Sylius CompleteType; constraints in Default would never run. */
    public const VALIDATION_GROUPS = ['sylius', 'sylius_checkout_complete'];

    public function __construct(
        private readonly ChannelContextInterface $channelContext,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getExtendedTypes(): iterable
    {
        return [CompleteType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('poReference', TextType::class, [
            'label' => 'app.form.checkout.po_reference',
            'required' => false,
            'attr' => ['maxlength' => OrderPoReferenceAwareInterface::PO_REFERENCE_MAX_LENGTH],
            'constraints' => [
                new Length(
                    max: OrderPoReferenceAwareInterface::PO_REFERENCE_MAX_LENGTH,
                    maxMessage: 'app.checkout.po_reference.max_length',
                    groups: self::VALIDATION_GROUPS,
                ),
                new Regex(
                    pattern: OrderPoReferenceAwareInterface::PO_REFERENCE_PATTERN,
                    message: 'app.checkout.po_reference.invalid_characters',
                    groups: self::VALIDATION_GROUPS,
                ),
            ],
        ]);

        if ($this->isCostCenterEnabled()) {
            $builder->add('costCenter', TextType::class, [
                'label' => 'app.form.checkout.cost_center',
                'required' => false,
                'attr' => ['maxlength' => OrderPoReferenceAwareInterface::COST_CENTER_MAX_LENGTH],
                'constraints' => [
                    new Length(max: OrderPoReferenceAwareInterface::COST_CENTER_MAX_LENGTH, groups: self::VALIDATION_GROUPS),
                    new Regex(
                        pattern: OrderPoReferenceAwareInterface::COST_CENTER_PATTERN,
                        message: 'app.checkout.cost_center.invalid_characters',
                        groups: self::VALIDATION_GROUPS,
                    ),
                ],
            ]);
        }

        $builder
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $form = $event->getForm();
                $order = $event->getData();

                if (!$order instanceof OrderPoReferenceAwareInterface) {
                    return;
                }

                $channel = $this->channelContext->getChannel();
                if (!$channel instanceof Channel) {
                    return;
                }

                if (!$channel->isPoReferenceRequired()) {
                    return;
                }

                $poReference = $order->getPoReference();
                if (null === $poReference || '' === trim($poReference)) {
                    $form->get('poReference')->addError(
                        new FormError($this->translator->trans('app.checkout.po_reference.required', [], 'validators')),
                    );
                }
            })
        ;
    }

    private function isCostCenterEnabled(): bool
    {
        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return false;
        }

        return $channel instanceof Channel && $channel->isCostCenterEnabled();
    }
}
