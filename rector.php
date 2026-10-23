<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony63\Rector\Class_\ParamAndEnvAttributeRector;

$builder = RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withSkip([
        // `#[Autowire('/%param%')]` carries a literal prefix; `param:` would silently drop the leading slash.
        ParamAndEnvAttributeRector::class => [
            __DIR__ . '/src/Demo/Admin/DemoAdminWriteGuard.php',
            __DIR__ . '/src/Demo/Admin/DemoAdminRefusal.php',
        ],
    ]);

// The sylius/sylius-rector extension turns on name importing, and the builder cannot turn it back off. This project
// writes global classes fully qualified (\DateTimeImmutable, \Throwable), which ECS enforces: importing them would
// only churn the code.
return static function (RectorConfig $rectorConfig) use ($builder): void {
    $builder($rectorConfig);
    $rectorConfig->importNames(false, false);
};
