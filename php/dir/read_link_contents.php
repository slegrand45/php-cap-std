<?php

namespace tests\dir;

function read_link_contents(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $target = 'target_file.txt';
    $linkName = 'link_to_target';
    $targetPath = $root . '/' . $target;
    file_put_contents($targetPath, 'symlink target content');

    try {
        $dir->symlink($target, $linkName);

        $readTarget = $dir->read_link_contents($linkName);
        if ($readTarget === $target) {
            ok('dir: read_link_contents: correctly reads verbatim symlink target');
        } else {
            ko("dir: read_link_contents: expected '$target', got '$readTarget'");
        }
    } catch (\Throwable $e) {
        ko('dir: read_link_contents: failed with exception: ' . $e->getMessage());
    } finally {
        if (file_exists($targetPath)) {
            unlink($targetPath);
        }
        $linkPath = $root . '/' . $linkName;
        if (is_link($linkPath) || file_exists($linkPath)) {
            unlink($linkPath);
        }
    }
}
