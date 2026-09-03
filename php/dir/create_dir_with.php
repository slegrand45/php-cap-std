<?php

namespace tests\dir;

function create_dir_with(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    try {
        $builder = \StephpCapStdDirBuilder::new();
        $builder->recursive(true);
        $builder->mode(0750);

        $path = 'nested/builder/dir';
        $dir->create_dir_with($path, $builder);

        $fullPath = $root . '/' . $path;
        if (is_dir($fullPath)) {
            ok('dir: create_dir_with: nested directories created with recursive builder');
        } else {
            ko('dir: create_dir_with: failed to create nested directories');
        }

        clearstatcache();
        $perms = fileperms($fullPath) & 0777;
        if ($perms === 0750) {
            ok('dir: create_dir_with: directory mode is 0750');
        } else {
            ok("dir: create_dir_with: directory mode is " . decoct($perms) . " (subject to umask)");
        }
    } catch (\Throwable $e) {
        ko('dir: create_dir_with: failed with exception: ' . $e->getMessage());
    } finally {
        $fullPath = $root . '/nested';
        if (is_dir($fullPath)) {
            recursive_rmdir($fullPath);
        }
    }
}
