<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Counters of the demo rate limiters (config/packages/test/demo_rate_limiter.yaml): empty at the start of each run.
$limiterStorage = dirname(__DIR__) . '/var/demo-limiter-test';
if (is_dir($limiterStorage)) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($limiterStorage, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if (!$file instanceof SplFileInfo) {
            continue;
        }
        $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
    }
}
