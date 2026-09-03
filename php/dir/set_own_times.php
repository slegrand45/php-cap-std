<?php

namespace tests\dir;

function set_own_times(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $now = time();
    $atime = \StephpCapStdSystemTime::from_unix_timestamp($now - 3600);
    $mtime = \StephpCapStdSystemTime::from_unix_timestamp($now - 7200);

    try {
        $dir->set_own_times($atime, $mtime);
        ok('dir: set_own_times: called successfully');

        clearstatcache();
        $stats = stat($root);
        if (abs($stats['atime'] - ($now - 3600)) <= 2) {
            ok('dir: set_own_times: atime matches');
        } else {
            ko("dir: set_own_times: atime mismatch, expected " . ($now - 3600) . " got " . $stats['atime']);
        }

        if (abs($stats['mtime'] - ($now - 7200)) <= 2) {
            ok('dir: set_own_times: mtime matches');
        } else {
            ko("dir: set_own_times: mtime mismatch, expected " . ($now - 7200) . " got " . $stats['mtime']);
        }
    } catch (\Throwable $e) {
        ko('dir: set_own_times: failed with exception: ' . $e->getMessage());
    }
}
