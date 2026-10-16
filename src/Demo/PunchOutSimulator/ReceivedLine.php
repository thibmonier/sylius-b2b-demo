<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/** One ItemIn of a PunchOutOrderMessage received by the simulator. */
final readonly class ReceivedLine
{
    public function __construct(
        public string $supplierPartId,
        public string $supplierPartAuxId,
        public string $description,
        public int $quantity,
        public string $unitPrice,
        public string $currency,
        public string $unitOfMeasure,
    ) {
    }

    /** @return array<string, int|string> */
    public function toArray(): array
    {
        return [
            'supplierPartId' => $this->supplierPartId,
            'supplierPartAuxId' => $this->supplierPartAuxId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'currency' => $this->currency,
            'unitOfMeasure' => $this->unitOfMeasure,
        ];
    }

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): ?self
    {
        foreach (['supplierPartId', 'supplierPartAuxId', 'description', 'unitPrice', 'currency', 'unitOfMeasure'] as $key) {
            if (!is_string($data[$key] ?? null)) {
                return null;
            }
        }
        if (!is_int($data['quantity'] ?? null)) {
            return null;
        }

        return new self($data['supplierPartId'], $data['supplierPartAuxId'], $data['description'], $data['quantity'], $data['unitPrice'], $data['currency'], $data['unitOfMeasure']);
    }
}
