<?php

namespace tests\dir;

function metadata_extended(string $root) {
    $ambient_auth = stephp_cap_std_ambient_authority();
    $dir = stephp_cap_std_open_ambient_dir($ambient_auth, $root);

    $filename = 'test-meta-ext.txt';
    $filepath = $root . '/' . $filename;
    file_put_contents($filepath, 'metadata extended test data');

    try {
        $meta = $dir->metadata($filename);

        if ($meta->dev() >= 0) {
            ok('dir: metadata_extended: dev() returns valid device id');
        } else {
            ko('dir: metadata_extended: dev() failed');
        }

        if ($meta->ino() > 0) {
            ok('dir: metadata_extended: ino() returns valid inode');
        } else {
            ko('dir: metadata_extended: ino() failed');
        }

        if ($meta->mode() > 0) {
            ok('dir: metadata_extended: mode() returns valid mode');
        } else {
            ko('dir: metadata_extended: mode() failed');
        }

        if ($meta->nlink() >= 1) {
            ok('dir: metadata_extended: nlink() returns valid link count');
        } else {
            ko('dir: metadata_extended: nlink() failed');
        }

        if ($meta->uid() >= 0) {
            ok('dir: metadata_extended: uid() returns valid uid');
        } else {
            ko('dir: metadata_extended: uid() failed');
        }

        if ($meta->gid() >= 0) {
            ok('dir: metadata_extended: gid() returns valid gid');
        } else {
            ko('dir: metadata_extended: gid() failed');
        }

        if ($meta->size() === strlen('metadata extended test data')) {
            ok('dir: metadata_extended: size() returns correct size');
        } else {
            ko('dir: metadata_extended: size() returned ' . $meta->size());
        }

        if ($meta->atime() > 0 && $meta->atime_nsec() >= 0) {
            ok('dir: metadata_extended: atime() and atime_nsec() are valid');
        } else {
            ko('dir: metadata_extended: atime/atime_nsec failed');
        }

        if ($meta->mtime() > 0 && $meta->mtime_nsec() >= 0) {
            ok('dir: metadata_extended: mtime() and mtime_nsec() are valid');
        } else {
            ko('dir: metadata_extended: mtime/mtime_nsec failed');
        }

        if ($meta->ctime() > 0 && $meta->ctime_nsec() >= 0) {
            ok('dir: metadata_extended: ctime() and ctime_nsec() are valid');
        } else {
            ko('dir: metadata_extended: ctime/ctime_nsec failed');
        }

        if ($meta->blksize() > 0) {
            ok('dir: metadata_extended: blksize() returns positive block size');
        } else {
            ko('dir: metadata_extended: blksize() failed');
        }

        if ($meta->blocks() >= 0) {
            ok('dir: metadata_extended: blocks() returns non-negative block count');
        } else {
            ko('dir: metadata_extended: blocks() failed');
        }
    } catch (\Throwable $e) {
        ko('dir: metadata_extended: failed with exception: ' . $e->getMessage());
    } finally {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}
