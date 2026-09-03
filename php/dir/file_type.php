<?php

namespace tests\dir;

function file_type(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $filename = 'test-file-type.txt';
    $filepath = $root . '/' . $filename;
    file_put_contents($filepath, 'file type content');

    $dirname = 'test-file-type-dir';
    $dirpath = $root . '/' . $dirname;
    mkdir($dirpath);

    $linkname = 'test-file-type-link';
    $linkpath = $root . '/' . $linkname;
    symlink($filepath, $linkpath);

    try {
        // File type of regular file
        $fileMeta = $dir->metadata($filename);
        $ftFile = $fileMeta->file_type();
        if ($ftFile->is_file() && ! $ftFile->is_dir() && ! $ftFile->is_symlink()) {
            ok('dir: file_type: regular file type correctly identified');
        } else {
            ko('dir: file_type: regular file type mismatch');
        }

        // File type of directory
        $dirMeta = $dir->metadata($dirname);
        $ftDir = $dirMeta->file_type();
        if ($ftDir->is_dir() && ! $ftDir->is_file() && ! $ftDir->is_symlink()) {
            ok('dir: file_type: directory type correctly identified');
        } else {
            ko('dir: file_type: directory type mismatch');
        }

        // File type of symlink via symlink_metadata
        $symMeta = $dir->symlink_metadata($linkname);
        $ftSym = $symMeta->file_type();
        if ($ftSym->is_symlink() && ! $ftSym->is_file() && ! $ftDir->is_file()) {
            ok('dir: file_type: symlink type correctly identified via symlink_metadata');
        } else {
            ko('dir: file_type: symlink type mismatch');
        }
    } catch (\Throwable $e) {
        ko('dir: file_type: failed with exception: ' . $e->getMessage());
    } finally {
        if (is_link($linkpath)) {
            unlink($linkpath);
        }
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        if (is_dir($dirpath)) {
            rmdir($dirpath);
        }
    }
}
