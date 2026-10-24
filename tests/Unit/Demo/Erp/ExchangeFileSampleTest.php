<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Erp;

use App\Demo\Erp\ExchangeFileSample;
use App\Demo\Erp\RecentPlacedOrders;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;
use Tmonier\SyliusWebhookOutboxPlugin\Entity\OutboxEvent;

/** US-190: the excerpt of the page is produced by the contract of the plugin from the placed orders of the demo, e-mails masked. */
final class ExchangeFileSampleTest extends TestCase
{
    public function testTheExcerptStartsWithTheHeaderOfTheContractAndHasOneLinePerOrderLine(): void
    {
        $lines = $this->lines((new ExchangeFileSample($this->orders($this->placed('00000001', 'claire.martin@acme.example'))))->csv());

        self::assertSame(implode(';', OrderLinesCsv::HEADER), $lines[0]);
        self::assertCount(1 + 2, $lines);
    }

    public function testTheEmailOfTheCustomerIsMasked(): void
    {
        $csv = (new ExchangeFileSample($this->orders($this->placed('00000001', 'claire.martin@acme.example'))))->csv();

        self::assertStringContainsString('c***@acme.example', $csv);
        self::assertStringNotContainsString('claire', $csv);
    }

    public function testWithoutPlacedOrdersTheExcerptIsBuiltFromFictitiousOnesWithExampleAddresses(): void
    {
        $csv = (new ExchangeFileSample($this->orders()))->csv();
        $lines = $this->lines($csv);

        self::assertGreaterThan(1, \count($lines));
        foreach (\array_slice($lines, 1) as $line) {
            $fields = explode(';', $line);
            self::assertCount(10, $fields);
            self::assertStringEndsWith('.example', $fields[4]);
        }
    }

    public function testAtMostThreeOrdersAreShown(): void
    {
        $orders = array_map(fn (int $n): OutboxEvent => $this->placed(sprintf('%08d', $n), 'buyer@acme.example'), range(1, 6));

        $csv = (new ExchangeFileSample($this->orders(...$orders)))->csv();

        self::assertLessThanOrEqual(3, \count(array_unique(array_map(static fn (string $line): string => explode(';', $line)[0], \array_slice($this->lines($csv), 1)))));
    }

    /** @return list<string> */
    private function lines(string $csv): array
    {
        return explode("\n", rtrim($csv, "\n"));
    }

    private function orders(OutboxEvent ...$events): RecentPlacedOrders
    {
        return new class(array_values($events)) implements RecentPlacedOrders {
            /** @param list<OutboxEvent> $events */
            public function __construct(private readonly array $events)
            {
            }

            public function latest(int $limit): array
            {
                return \array_slice($this->events, 0, $limit);
            }
        };
    }

    private function placed(string $number, string $email): OutboxEvent
    {
        return new OutboxEvent(
            'order.placed',
            1,
            new \DateTimeImmutable('2026-10-20T10:00:00Z'),
            [
                'number' => $number,
                'currency_code' => 'EUR',
                'checkout_completed_at' => '2026-10-20T12:00:00+02:00',
                'customer' => ['email' => $email],
                'shipping_address' => ['country_code' => 'FR'],
                'items' => [
                    ['variant_code' => 'PAPER-A4', 'quantity' => 10, 'unit_price' => 450, 'total' => 4500],
                    ['variant_code' => 'PEN-BLUE', 'quantity' => 5, 'unit_price' => 120, 'total' => 600],
                ],
            ],
            'WEB',
            'order',
            '1',
            null,
            '0192a000-0000-7000-8000-' . str_pad($number, 12, '0', \STR_PAD_LEFT),
        );
    }
}
