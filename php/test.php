<?php

// Test runner for stephp-cap-std
// Usage: php -d extension=../target/release/libstephp_cap_std.so test.php [--filter=<pattern>]

require_once(__DIR__ . '/common.php');

// Parse CLI options
$options = getopt('', ['filter:']);
$filter = $options['filter'] ?? null;

$ROOT = sys_get_temp_dir() . '/php-cap-std-tests-' . uniqid();
if (! mkdir($ROOT)) {
    die("Unable to create test root directory: $ROOT\n");
}

// Discover all PHP test files
$testFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $relPath = substr($file->getPathname(), strlen(__DIR__) + 1);
        // Exclude runner and common helpers
        if ($relPath === 'test.php' || $relPath === 'common.php') {
            continue;
        }
        $testFiles[$relPath] = $file->getPathname();
    }
}

// Sort alphabetically for deterministic test order
ksort($testFiles);

try {
    foreach ($testFiles as $relPath => $filePath) {
        // Apply filter if specified
        if ($filter !== null && stripos($relPath, $filter) === false) {
            continue;
        }

        $functionsBefore = get_defined_functions()['user'];
        require_once($filePath);
        $functionsAfter = get_defined_functions()['user'];
        $newFunctions = array_diff($functionsAfter, $functionsBefore);

        // Run all newly defined functions in the namespace
        foreach ($newFunctions as $func) {
            if (str_starts_with(strtolower($func), 'tests\\')) {
                $ref = new ReflectionFunction($func);
                if ($ref->getNumberOfRequiredParameters() === 0) {
                    $func();
                } else {
                    $func($ROOT);
                }
            }
        }
    }
} finally {
    recursive_rmdir($ROOT);
}

result();
