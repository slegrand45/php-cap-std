<?php

namespace tests\dir;

function open_options_custom_flags(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $filename = 'test-custom-flags.txt';
    $filepath = $root . '/' . $filename;
    file_put_contents($filepath, 'flags test content');

    try {
        $opts = \StephpCapStdOpenOptions::new();
        $opts->read(true);
        // O_CLOEXEC is typically 02000000 on Linux, or 0 for basic test
        $opts->custom_flags(0);

        $file = $dir->open_with($filename, $opts);
        $content = $file->read_to_string();
        if ($content === 'flags test content') {
            ok('dir: open_options_custom_flags: open_with using custom_flags works');
        } else {
            ko("dir: open_options_custom_flags: unexpected content '$content'");
        }
    } catch (\Throwable $e) {
        ko('dir: open_options_custom_flags: failed with exception: ' . $e->getMessage());
    } finally {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}
