<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$tests = [
    'AuthFlowTest' => require __DIR__ . '/AuthFlowTest.php',
    'CatalogAndFavoriteTest' => require __DIR__ . '/CatalogAndFavoriteTest.php',
    'RatingDownloadPlaylistTest' => require __DIR__ . '/RatingDownloadPlaylistTest.php',
    'ProjectServicesTest' => require __DIR__ . '/ProjectServicesTest.php',
    'PlaylistPageServiceTest' => require __DIR__ . '/PlaylistPageServiceTest.php',
    'HttpServicesTest' => require __DIR__ . '/HttpServicesTest.php',
    'ContactFormServiceTest' => require __DIR__ . '/ContactFormServiceTest.php',
    'CsrfTest' => require __DIR__ . '/CsrfTest.php',
    'MediaUploaderTest' => require __DIR__ . '/MediaUploaderTest.php',
];

$failures = 0;

foreach ($tests as $name => $test) {
    try {
        $test();
        echo "[OK] {$name}\n";
    } catch (Throwable $e) {
        $failures++;
        echo "[FAIL] {$name}: {$e->getMessage()}\n";
    }
}

if ($failures > 0) {
    exit(1);
}

echo "All tests passed.\n";
