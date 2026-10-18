<?php

declare(strict_types=1);

namespace App\Demo\Order;

use App\Order\OrderPoReferenceAwareInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\OrderExampleFactory;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

/**
 * Sylius' order example factory with the random parts removed (US-011): the lines come from the
 * `items` option, the addresses from the customer, the randomly picked methods and locale from a seeded generator.
 */
final class DeterministicOrderFactory extends OrderExampleFactory
{
    private const SHIPPING_METHOD = 'carrier';

    private const PAYMENT_METHOD = 'bank_transfer';

    private ?string $paymentMethodCode = null;

    /** @var array<string, int> */
    private array $items = [];

    /** @var array<string, ProductVariantInterface> variants by code, read once for the whole history (see {@see self::preloadVariants()}) */
    private array $variants = [];

    /** The same shipping and payment methods serve every order: read once (see {@see self::forget()}). */
    private ?ShippingMethodInterface $shippingMethod = null;

    /** @var array<string, PaymentMethodInterface> by code */
    private array $paymentMethods = [];

    private ?string $company = null;

    /** @var RepositoryInterface<ProductVariantInterface>|null */
    private ?RepositoryInterface $variantRepository = null;

    /** @param RepositoryInterface<ProductVariantInterface> $variantRepository */
    public function setVariantRepository(RepositoryInterface $variantRepository): void
    {
        $this->variantRepository = $variantRepository;
    }

    /**
     * Reads these variants in one query instead of one lookup per order line. Call it before {@see self::create()};
     * {@see self::forget()} drops what it holds (the entities do not outlive a purge).
     *
     * @param list<string> $codes
     */
    public function preloadVariants(array $codes): void
    {
        if (null === $this->variantRepository) {
            throw new \LogicException('A demo order needs its variant repository.');
        }

        foreach ($this->variantRepository->findBy(['code' => \array_values(\array_unique($codes))]) as $variant) {
            $this->variants[(string) $variant->getCode()] = $variant;
        }
    }

    public function forget(): void
    {
        $this->variants = [];
        $this->shippingMethod = null;
        $this->paymentMethods = [];
    }

    public function create(array $options = []): OrderInterface
    {
        /** @var array<string, int> $items */
        $items = $options['items'] ?? [];
        /** @var string|null $poReference */
        $poReference = $options['po_reference'] ?? null;
        /** @var string|null $costCenter */
        $costCenter = $options['cost_center'] ?? null;
        /** @var string|null $company */
        $company = $options['company'] ?? null;
        /** @var string|null $paymentMethod */
        $paymentMethod = $options['payment_method'] ?? null;
        $this->items = $items;
        $this->company = $company;
        $this->paymentMethodCode = $paymentMethod;
        unset($options['items'], $options['po_reference'], $options['cost_center'], $options['company'], $options['payment_method']);

        $this->faker->seed(crc32((string) $poReference));
        $order = parent::create($options);
        if ($order instanceof OrderPoReferenceAwareInterface) {
            $order->setPoReference($poReference);
            $order->setCostCenter($costCenter);
        }

        return $order;
    }

    protected function generateItems(OrderInterface $order): void
    {
        if (null === $this->variantRepository || [] === $this->items) {
            throw new \LogicException('A demo order needs its variant repository and at least one line.');
        }

        // Placed by its signed-in customer from the start: the checkout completes right after the items, and payment on
        // account (trade credit) refuses an order created as a guest.
        $customer = $order->getCustomer();
        if ($customer instanceof CustomerInterface && $customer->hasUser()) {
            $order->setCustomerWithAuthorization($customer);
        }

        foreach ($this->items as $code => $quantity) {
            $variant = $this->variants[$code] ?? $this->variantRepository->findOneBy(['code' => $code]);
            if (!$variant instanceof ProductVariantInterface) {
                throw new \InvalidArgumentException(sprintf('Demo order: variant "%s" does not exist.', $code));
            }
            $item = $this->orderItemFactory->createNew();
            $item->setVariant($variant);
            $this->orderItemQuantityModifier->modify($item, $quantity);
            $order->addItem($item);
        }
    }

    protected function address(OrderInterface $order, string $countryCode): void
    {
        $customer = $order->getCustomer();
        $address = $this->addressFactory->createNew();
        $address->setFirstName((string) $customer?->getFirstName());
        $address->setLastName((string) $customer?->getLastName());
        $address->setCompany($this->company);
        $address->setStreet('12 rue de l\'Industrie');
        $address->setCountryCode($countryCode);
        $address->setCity('Lyon');
        $address->setPostcode('69003');

        $order->setShippingAddress($address);
        $order->setBillingAddress(clone $address);

        $this->applyCheckoutStateTransition($order, OrderCheckoutTransitions::TRANSITION_ADDRESS);
    }

    protected function selectShipping(OrderInterface $order, \DateTimeInterface $createdAt): void
    {
        $method = $this->shippingMethod ??= $this->shippingMethodRepository->findOneBy(['code' => self::SHIPPING_METHOD]);
        if (null === $method) {
            throw new \InvalidArgumentException(sprintf('Demo order: shipping method "%s" does not exist.', self::SHIPPING_METHOD));
        }
        foreach ($order->getShipments() as $shipment) {
            $shipment->setMethod($method);
            $shipment->setCreatedAt($createdAt);
        }

        $this->applyCheckoutStateTransition($order, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);
    }

    /** Bank transfer unless the order asks for another method: paid on account, it reserves trade credit (an open item). */
    protected function selectPayment(OrderInterface $order, \DateTimeInterface $createdAt): void
    {
        $code = $this->paymentMethodCode ?? self::PAYMENT_METHOD;
        $method = $this->paymentMethods[$code] ?? $this->paymentMethodRepository->findOneBy(['code' => $code]);
        if (null === $method) {
            throw new \InvalidArgumentException(sprintf('Demo order: payment method "%s" does not exist.', $code));
        }
        $this->paymentMethods[$code] = $method;
        foreach ($order->getPayments() as $payment) {
            $payment->setMethod($method);
            $payment->setCreatedAt($createdAt);
        }

        $this->applyCheckoutStateTransition($order, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT);
    }
}
