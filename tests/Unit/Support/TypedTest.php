<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Plugin\DemoPluginCatalog;
use App\Tests\Support\DemoCatalogs;
use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;

/** US-174: tests read decoded YAML and JSON through checked accessors instead of indexing into `mixed`. */
final class TypedTest extends TestCase
{
    public function testItReadsANestedValueByKeysEvenWhenAKeyHoldsADot(): void
    {
        $data = ['parameters' => ['demo.password' => 'secret']];

        self::assertSame('secret', Typed::string(Typed::at($data, 'parameters', 'demo.password'), 'password'));
    }

    public function testAMissingKeyFailsAndNamesThePath(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('parameters > demo.missing');

        Typed::at(['parameters' => []], 'parameters', 'demo.missing');
    }

    public function testAListOfMapsIsCheckedEntryByEntry(): void
    {
        self::assertSame([['code' => 'A']], Typed::listOfMaps([['code' => 'A']], 'companies'));

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('companies');

        Typed::listOfMaps([['code' => 'A'], 'not a map'], 'companies');
    }

    public function testAMapRefusesAList(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        Typed::map(['a', 'b'], 'config');
    }

    public function testAStringRefusesAnythingElse(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('version');

        Typed::string(['8.5'], 'version');
    }

    public function testTheDemoCatalogsAreBuiltFromTheConfiguration(): void
    {
        self::assertInstanceOf(DemoAccountCatalog::class, DemoCatalogs::accounts());
        self::assertGreaterThan(0, DemoCatalogs::plugins()->count());
        self::assertInstanceOf(DemoPluginCatalog::class, DemoCatalogs::plugins());
    }
}
