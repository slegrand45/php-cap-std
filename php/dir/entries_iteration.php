<?php

namespace tests\dir;

function entries_iteration(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $subdirName = 'test-iter-subdir';
    $subdirPath = $root . '/' . $subdirName;
    mkdir($subdirPath);

    $files = ['iter_a.txt', 'iter_b.txt', 'iter_c.txt'];
    foreach ($files as $f) {
        file_put_contents($subdirPath . '/' . $f, 'test');
    }

    try {
        $subDirObj = $dir->open_dir($subdirName);
        $entries = $subDirObj->entries();

        // 1. Countable
        if (count($entries) === count($files)) {
            ok('dir: entries_iteration: count() returns correct number of items');
        } else {
            ko('dir: entries_iteration: count() expected ' . count($files) . ', got ' . count($entries));
        }

        // 2. Foreach loop
        $collected = [];
        foreach ($entries as $key => $name) {
            $collected[$key] = $name;
        }
        if (count($collected) === count($files)) {
            ok('dir: entries_iteration: foreach traversed all entries');
        } else {
            ko('dir: entries_iteration: foreach failed to traverse all entries');
        }

        // 3. Manual Iterator methods: rewind, valid, current, key, next
        $entries->rewind();
        $manual = [];
        while ($entries->valid()) {
            $manual[$entries->key()] = $entries->current();
            $entries->next();
        }
        if ($manual === $collected) {
            ok('dir: entries_iteration: manual Iterator methods produce same results as foreach');
        } else {
            ko('dir: entries_iteration: manual Iterator mismatch');
        }
    } catch (\Throwable $e) {
        ko('dir: entries_iteration: failed with exception: ' . $e->getMessage());
    } finally {
        if (is_dir($subdirPath)) {
            recursive_rmdir($subdirPath);
        }
    }
}
