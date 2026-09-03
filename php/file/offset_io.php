<?php

namespace tests\file;

function offset_io(string $root) {
    $filename = 'test-offset-io.txt';
    $filepath = $root . '/' . $filename;
    file_put_contents($filepath, '0123456789abcdef');

    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $opts = \StephpCapStdOpenOptions::new();
    $opts->read(true);
    $opts->write(true);

    $file = $dir->open_with($filename, $opts);

    try {
        // read_at
        $readData = $file->read_at(4, 5); // offset 5: '5678'
        if ($readData === '5678') {
            ok('file: offset_io: read_at(4, 5) returned expected bytes');
        } else {
            ko("file: offset_io: read_at expected '5678', got '$readData'");
        }

        // Check that cursor was not moved by read_at
        $pos = $file->stream_position();
        if ($pos === 0) {
            ok('file: offset_io: read_at did not change cursor position');
        } else {
            ko("file: offset_io: cursor position expected 0, got $pos");
        }

        // read_exact_at
        $readExact = $file->read_exact_at(3, 10); // offset 10: 'abc'
        if ($readExact === 'abc') {
            ok('file: offset_io: read_exact_at(3, 10) returned expected bytes');
        } else {
            ko("file: offset_io: read_exact_at expected 'abc', got '$readExact'");
        }

        // write_at
        $written = $file->write_at('XYZ', 2);
        if ($written === 3) {
            ok('file: offset_io: write_at returned 3 bytes written');
        } else {
            ko("file: offset_io: write_at expected 3, got $written");
        }

        // Verify written data at offset
        $verify = $file->read_at(5, 0); // '01XYZ'
        if ($verify === '01XYZ') {
            ok('file: offset_io: write_at correctly modified file contents at offset');
        } else {
            ko("file: offset_io: expected '01XYZ', got '$verify'");
        }

        // write_all_at
        $file->write_all_at('WXYZ', 10);
        $verifyEnd = $file->read_at(6, 10); // 'WXYZef'
        if ($verifyEnd === 'WXYZef') {
            ok('file: offset_io: write_all_at correctly wrote all bytes at offset');
        } else {
            ko("file: offset_io: expected 'WXYZef', got '$verifyEnd'");
        }

        // Cursor should still be at 0
        if ($file->stream_position() === 0) {
            ok('file: offset_io: cursor position remained 0 after all offset operations');
        } else {
            ko('file: offset_io: cursor position was modified unexpectedly');
        }
    } catch (\Throwable $e) {
        ko('file: offset_io: failed with exception: ' . $e->getMessage());
    } finally {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}
