<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use App\Demo\Account\DemoAccountCatalog;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\RouterInterface;

/**
 * The guided journeys, read from config/demo/journeys.yaml (US-105). Validated when first used: a step whose route does
 * not exist or a journey whose account is unknown (or disabled) is refused with a message that names the journey and the step.
 */
final class JourneyCatalog
{
    /** @var array<string, Journey> */
    private array $journeys = [];

    /** @param array<array-key, mixed> $definitions */
    public function __construct(
        #[Autowire(param: 'demo.journeys')]
        array $definitions,
        DemoAccountCatalog $accounts,
        RouterInterface $router,
    ) {
        foreach ($definitions as $id => $definition) {
            $journey = self::build((string) $id, $definition);
            self::assertAccount($journey, $accounts);
            self::assertRoutes($journey, $router);
            $this->journeys[$journey->id] = $journey;
        }
    }

    /** @return list<Journey> */
    public function all(): array
    {
        return array_values($this->journeys);
    }

    public function find(string $id): ?Journey
    {
        return $this->journeys[$id] ?? null;
    }

    private static function build(string $id, mixed $definition): Journey
    {
        if (1 !== preg_match('/^[a-z0-9][a-z0-9_-]{0,39}$/', $id) || !\is_array($definition)) {
            throw new \InvalidArgumentException(\sprintf('Journey id "%s" is invalid.', $id));
        }
        $steps = [];
        $rows = $definition['steps'] ?? null;
        foreach (\is_array($rows) ? array_values($rows) : [] as $index => $row) {
            $steps[] = self::step($id, $index + 1, $row);
        }

        return new Journey(
            $id,
            self::string($definition, 'persona', $id),
            self::string($definition, 'account', $id),
            max(1, self::minutes($definition['minutes'] ?? null)),
            $steps,
        );
    }

    private static function step(string $journey, int $number, mixed $row): JourneyStep
    {
        if (!\is_array($row)) {
            throw new \InvalidArgumentException(\sprintf('Journey "%s" step %d must be a map.', $journey, $number));
        }
        $scenario = $row['scenario'] ?? null;

        return new JourneyStep(
            $number,
            self::string($row, 'route', \sprintf('%s step %d', $journey, $number)),
            self::strings($row['route_parameters'] ?? []),
            \is_string($scenario) ? $scenario : null,
            self::strings($row['scenario_options'] ?? []),
        );
    }

    private static function minutes(mixed $value): int
    {
        return \is_int($value) ? $value : 5;
    }

    /** @return array<string, string> */
    private static function strings(mixed $values): array
    {
        $strings = [];
        foreach (\is_array($values) ? $values : [] as $key => $value) {
            if (\is_scalar($value)) {
                $strings[(string) $key] = (string) $value;
            }
        }

        return $strings;
    }

    /** @param array<array-key, mixed> $data */
    private static function string(array $data, string $key, string $where): string
    {
        $value = $data[$key] ?? null;
        if (!\is_string($value) || '' === $value) {
            throw new \InvalidArgumentException(\sprintf('Journey "%s": key "%s" must be a non-empty string.', $where, $key));
        }

        return $value;
    }

    private static function assertAccount(Journey $journey, DemoAccountCatalog $accounts): void
    {
        $account = $accounts->findShopAccount($journey->accountEmail);
        if (null === $account || !$account->enabled) {
            throw new \InvalidArgumentException(\sprintf('Journey "%s": the account "%s" is unknown or disabled.', $journey->id, $journey->accountEmail));
        }
    }

    private static function assertRoutes(Journey $journey, RouterInterface $router): void
    {
        $routes = $router->getRouteCollection();
        foreach ($journey->steps as $step) {
            if (null === $routes->get($step->route)) {
                throw new \InvalidArgumentException(\sprintf('Journey "%s" step %d: the route "%s" does not exist.', $journey->id, $step->number, $step->route));
            }
        }
    }
}
