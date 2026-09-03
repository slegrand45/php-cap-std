<?php

namespace tests\file;

function constants(string $root) {
    if (\StephpCapStdFile::SEEK_SET === 0) {
        ok('file: constants: StephpCapStdFile::SEEK_SET is 0');
    } else {
        ko('file: constants: StephpCapStdFile::SEEK_SET expected 0');
    }

    if (\StephpCapStdFile::SEEK_CUR === 1) {
        ok('file: constants: StephpCapStdFile::SEEK_CUR is 1');
    } else {
        ko('file: constants: StephpCapStdFile::SEEK_CUR expected 1');
    }

    if (\StephpCapStdFile::SEEK_END === 2) {
        ok('file: constants: StephpCapStdFile::SEEK_END is 2');
    } else {
        ko('file: constants: StephpCapStdFile::SEEK_END expected 2');
    }

    $filename = 'test-file-constants.txt';
    $filepath = $root . '/' . $filename;
    file_put_contents($filepath, '0123456789');

    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);
    $file = $dir->open($filename);

    $pos = $file->seek(4, \StephpCapStdFile::SEEK_SET);
    if ($pos === 4) {
        ok('file: constants: seek using StephpCapStdFile::SEEK_SET works');
    } else {
        ko("file: constants: seek returned $pos, expected 4");
    }

    if (file_exists($filepath)) {
        unlink($filepath);
    }
}
