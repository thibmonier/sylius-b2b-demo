<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Entity\Channel\Channel;
use App\Order\OrderPoReferenceAwareInterface;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Sylius\Component\Channel\Context\ChannelContextInterface;
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
        $builder
            ->add('poReference', TextType::class, [
                'label' => 'app.form.checkout.po_reference',
                'required' => false,
                'attr' => ['maxlength' => 64],
                'constraints' => [
                    new Length(max: 64, maxMessage: 'app.checkout.po_reference.max_length'),
                    new Regex(
                        pattern: '/^[A-Za-z0-9\-]*$/',
                        message: 'app.checkout.po_reference.invalid_characters',
                    ),
                ],
            ])
            ->add('costCenter', TextType::class, [
                'label' => 'app.form.checkout.cost_center',
                'required' => false,
                'attr' => ['maxlength' => 50],
                'constraints' => [
                    new Length(max: 50),
                ],
            ])
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
}
