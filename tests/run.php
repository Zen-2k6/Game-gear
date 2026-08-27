<?php
declare(strict_types=1);

$tests = [__DIR__ . '/ArchitectureTest.php', __DIR__ . '/RouterTest.php', __DIR__ . '/ViewRenderTest.php'];
foreach ($tests as $test) {
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($test), $status);
    if ($status !== 0) {
        exit($status);
    }
}

echo "All tests passed." . PHP_EOL;
