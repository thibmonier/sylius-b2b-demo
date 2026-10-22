<?php

declare(strict_types=1);

namespace App\Demo\Integration;

use App\Demo\Plugin\DemoPlugin;

/** One integration of the guide page (US-108): the plugin that covers it, or the plugin whose event contract it builds on. */
final readonly class Integration
{
    public const AVAILABLE = 'available';

    public const IN_PROGRESS = 'in_progress';

    public const UPCOMING = 'upcoming';

    public const NOT_PROVIDED = 'not_provided';

    public function __construct(
        public string $id,
        public string $state,
        public ?DemoPlugin $plugin,
        public ?DemoPlugin $contract,
    ) {
    }
}
