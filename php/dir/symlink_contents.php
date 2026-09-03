<?php

namespace tests\dir;

function symlink_contents(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $target = 'custom_target_path';
    $linkName = 'custom_link';

    try {
        $dir->symlink_contents($target, $linkName);

        $readTarget = $dir->read_link_contents($linkName);
        if ($readTarget === $target) {
            ok('dir: symlink_contents: symlink created and verbatim content read back');
        } else {
            ko("dir: symlink_contents: expected '$target', got '$readTarget'");
        }
    } catch (\Throwable $e) {
        ko('dir: symlink_contents: failed with exception: ' . $e->getMessage());
    } finally {
        $linkPath = $root . '/' . $linkName;
        if (is_link($linkPath) || file_exists($linkPath)) {
            unlink($linkPath);
        }
    }
}
