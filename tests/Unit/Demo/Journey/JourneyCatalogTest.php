<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Journey;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Journey\JourneyCatalog;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/** US-105: the journeys are validated when they are loaded; a bad step or account is named in the message. */
final class JourneyCatalogTest extends TestCase
{
    public function testAJourneyOfFourStepsIsLoadedWithItsNavigation(): void
    {
        $journey = $this->catalog(['demo' => $this->definition()])->find('demo');

        self::assertNotNull($journey);
        self::assertSame(4, $journey->stepCount());
        self::assertSame(5, $journey->exitNumber());
        $first = $journey->step(1);
        $third = $journey->step(3);
        self::assertNotNull($first);
        self::assertNotNull($third);
        self::assertSame('shop_a', $first->route);
        self::assertSame('cart', $third->scenario);
        self::assertSame(['list' => 'Atelier'], $third->scenarioOptions);
        self::assertNull($journey->step(0), 'There is no step 0.');
        self::assertNull($journey->step(5), 'The exit screen is not a step.');
        self::assertTrue($journey->isExit(5));
        self::assertFalse($journey->isExit(4));
    }

    public function testTheJourneysKeepTheirOrderAndAnUnknownIdIsNull(): void
    {
        $catalog = $this->catalog(['b' => $this->definition(), 'a' => $this->definition()]);

        self::assertSame(['b', 'a'], array_map(static fn ($journey): string => $journey->id, $catalog->all()));
        self::assertNull($catalog->find('nope'));
    }

    public function testAStepWithoutAnExistingRouteIsRefusedAndNamed(): void
    {
        $definition = $this->definition();
        $definition['steps'][1]['route'] = 'deleted_page';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Journey "demo" step 2: the route "deleted_page" does not exist.');

        $this->catalog(['demo' => $definition]);
    }

    public function testAnUnknownAccountIsRefused(): void
    {
        $definition = $this->definition();
        $definition['account'] = 'nobody@acme.example';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('the account "nobody@acme.example" is unknown or disabled');

        $this->catalog(['demo' => $definition]);
    }

    public function testADisabledAccountIsRefused(): void
    {
        $definition = $this->definition();
        $definition['account'] = 'off@acme.example';

        $this->expectException(\InvalidArgumentException::class);

        $this->catalog(['demo' => $definition]);
    }

    public function testAJourneyWithoutStepIsRefused(): void
    {
        $definition = $this->definition();
        $definition['steps'] = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('has no step');

        $this->catalog(['demo' => $definition]);
    }

    public function testAnInvalidIdIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->catalog(['Bad Id!' => $this->definition()]);
    }

    /** @return array{persona: string, account: string, minutes: int, steps: list<array<string, mixed>>} */
    private function definition(): array
    {
        return [
            'persona' => 'buyer',
            'account' => 'claire@acme.example',
            'minutes' => 6,
            'steps' => [
                ['route' => 'shop_a'],
                ['route' => 'shop_b'],
                ['route' => 'shop_a', 'scenario' => 'cart', 'scenario_options' => ['list' => 'Atelier']],
                ['route' => 'shop_b'],
            ],
        ];
    }

    /** @param array<string, mixed> $definitions */
    private function catalog(array $definitions): JourneyCatalog
    {
        $routes = new RouteCollection();
        $routes->add('shop_a', new Route('/a'));
        $routes->add('shop_b', new Route('/b'));
        $router = $this->createStub(RouterInterface::class);
        $router->method('getRouteCollection')->willReturn($routes);

        $accounts = new DemoAccountCatalog('x', [[
            'code' => 'ACME',
            'name' => 'Acme',
            'members' => [
                ['email' => 'claire@acme.example', 'first_name' => 'Claire', 'last_name' => 'D', 'role' => 'buyer'],
                ['email' => 'off@acme.example', 'first_name' => 'Off', 'last_name' => 'D', 'role' => 'buyer', 'enabled' => false],
            ],
        ]], [], []);

        return new JourneyCatalog($definitions, $accounts, $router);
    }
}
