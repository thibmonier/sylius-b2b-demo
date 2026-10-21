<?php

declare(strict_types=1);

namespace App\Demo\Journey;

/** A scenario could not be prepared for the signed-in visitor (the journey page then says so instead of failing). */
final class ScenarioPreparationFailed extends \RuntimeException
{
}
