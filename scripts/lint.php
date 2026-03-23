<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$ignorePrefixes = [
    $root . '/vendor/',
    $root . '/.git/',
];

$errors = 0;

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $ignored = false;
    foreach ($ignorePrefixes as $prefix) {
        if (str_starts_with($path, $prefix)) {
            $ignored = true;
            break;
        }
    }

    if ($ignored) {
        continue;
    }

    $command = sprintf('php -l %s 2>&1', escapeshellarg($path));
    exec($command, $output, $exitCode);
    if ($exitCode !== 0) {
        $errors++;
        echo implode(PHP_EOL, $output) . PHP_EOL;
    }
}

if ($errors > 0) {
    exit(1);
}

echo "Lint passed.\n";
