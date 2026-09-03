<?php

namespace tests;

function error_messages(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    // 1. Non-existent file open throws exception
    $caught = false;
    try {
        $dir->open('non_existent_file_' . uniqid());
    } catch (\Throwable $e) {
        $caught = true;
    }
    if ($caught) {
        ok('errors: opening non-existent file throws exception');
    } else {
        ko('errors: opening non-existent file should throw exception');
    }

    // 2. Non-existent file read_to_string throws exception
    $caught = false;
    try {
        $dir->read_to_string('non_existent_file_' . uniqid());
    } catch (\Throwable $e) {
        $caught = true;
    }
    if ($caught) {
        ok('errors: reading non-existent file throws exception');
    } else {
        ko('errors: reading non-existent file should throw exception');
    }

    // 3. open_ambient_dir on invalid directory path throws exception
    $caught = false;
    try {
        stephp_cap_std_open_ambient_dir($ambient_auth, '/non/existent/path/' . uniqid());
    } catch (\Throwable $e) {
        $caught = true;
    }
    if ($caught) {
        ok('errors: open_ambient_dir on invalid path throws exception');
    } else {
        ko('errors: open_ambient_dir on invalid path should throw exception');
    }
}
