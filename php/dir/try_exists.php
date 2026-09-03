<?php

namespace tests\dir;

function try_exists(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $filename = 'test-stephp-cap-std-try-exists.txt';
    $filepath = $root . '/' . $filename;
    file_put_contents($filepath, 'try_exists content');

    try {
        if ($dir->try_exists($filename) === true) {
            ok('dir: try_exists: existing file returns true');
        } else {
            ko('dir: try_exists: existing file expected true');
        }

        if ($dir->try_exists('nonexistent-' . uniqid()) === false) {
            ok('dir: try_exists: non-existent file returns false');
        } else {
            ko('dir: try_exists: non-existent file expected false');
        }

        $subdir = 'test-try-exists-subdir';
        $subdirpath = $root . '/' . $subdir;
        mkdir($subdirpath);

        if ($dir->try_exists($subdir) === true) {
            ok('dir: try_exists: existing directory returns true');
        } else {
            ko('dir: try_exists: existing directory expected true');
        }

        rmdir($subdirpath);
    } catch (\Throwable $e) {
        ko('dir: try_exists: unexpected exception: ' . $e->getMessage());
    } finally {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}
