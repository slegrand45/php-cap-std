<?php

namespace tests\file;

function seek_invalid(string $root) {
    $filename = 'test-seek-invalid.txt';
    $filepath = $root . '/' . $filename;
    file_put_contents($filepath, 'seek invalid test');

    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);
    $file = $dir->open($filename);

    try {
        // 1. Negative offset with SEEK_SET must throw
        $caught = false;
        try {
            $file->seek(-5, \StephpCapStdFile::SEEK_SET);
        } catch (\Throwable $e) {
            $caught = true;
        }
        if ($caught) {
            ok('file: seek_invalid: negative offset with SEEK_SET throws exception');
        } else {
            ko('file: seek_invalid: negative offset with SEEK_SET should throw exception');
        }

        // 2. Invalid whence value must throw
        $caught = false;
        try {
            $file->seek(0, 99);
        } catch (\Throwable $e) {
            $caught = true;
        }
        if ($caught) {
            ok('file: seek_invalid: invalid whence value throws exception');
        } else {
            ko('file: seek_invalid: invalid whence value should throw exception');
        }
    } finally {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}
