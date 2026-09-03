<?php

namespace tests\dir;

function set_times(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    // 1. Test set_times on a file
    $filename = 'test-dir-set-times-file.txt';
    $filepath = $root . '/' . $filename;
    file_put_contents($filepath, 'file set_times');

    $now = time();
    $atime = \StephpCapStdSystemTime::from_unix_timestamp($now - 1800);
    $mtime = \StephpCapStdSystemTime::from_unix_timestamp($now - 3600);

    try {
        $dir->set_times($filename, $atime, $mtime);
        ok('dir: set_times: file timestamp updated successfully');

        clearstatcache();
        $stats = stat($filepath);
        if (abs($stats['atime'] - ($now - 1800)) <= 2) {
            ok('dir: set_times: file atime matches');
        } else {
            ko("dir: set_times: file atime mismatch, expected " . ($now - 1800) . " got " . $stats['atime']);
        }

        if (abs($stats['mtime'] - ($now - 3600)) <= 2) {
            ok('dir: set_times: file mtime matches');
        } else {
            ko("dir: set_times: file mtime mismatch, expected " . ($now - 3600) . " got " . $stats['mtime']);
        }
    } catch (\Throwable $e) {
        ko('dir: set_times: file test failed with exception: ' . $e->getMessage());
    } finally {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // 2. Test set_times on a directory
    $subdir = 'test-dir-set-times-subdir';
    $subdirpath = $root . '/' . $subdir;
    mkdir($subdirpath);

    try {
        $dir->set_times($subdir, $atime, $mtime);
        ok('dir: set_times: directory timestamp updated successfully');

        clearstatcache();
        $stats = stat($subdirpath);
        if (abs($stats['atime'] - ($now - 1800)) <= 2) {
            ok('dir: set_times: directory atime matches');
        } else {
            ko("dir: set_times: directory atime mismatch, expected " . ($now - 1800) . " got " . $stats['atime']);
        }

        if (abs($stats['mtime'] - ($now - 3600)) <= 2) {
            ok('dir: set_times: directory mtime matches');
        } else {
            ko("dir: set_times: directory mtime mismatch, expected " . ($now - 3600) . " got " . $stats['mtime']);
        }
    } catch (\Throwable $e) {
        ko('dir: set_times: directory test failed with exception: ' . $e->getMessage());
    } finally {
        if (is_dir($subdirpath)) {
            rmdir($subdirpath);
        }
    }
}
