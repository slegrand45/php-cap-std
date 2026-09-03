<?php

// Stubs for stephp-cap-std
//
// NOTE: extension classes cannot be instantiated with `new` from PHP.
// Objects are obtained through static constructors (`new()`,
// `from_unix_timestamp()`) or from other methods (e.g. `open_ambient_dir()`).
//
// Fallible methods throw a plain \Exception carrying the Rust error message.

namespace {
    /**
     * Returns the ambient authority handle, required to open entry-point
     * directories with stephp_cap_std_open_ambient_dir().
     */
    function stephp_cap_std_ambient_authority(): \StephpCapStdAmbientAuthority {}

    /**
     * Opens a directory using the host ambient authority. This is the only
     * way to obtain a StephpCapStdDir; all further operations are sandboxed
     * to that directory tree.
     *
     * @throws \Exception when the directory cannot be opened
     */
    function stephp_cap_std_open_ambient_dir(\StephpCapStdAmbientAuthority $auth, string $path): \StephpCapStdDir {}

    /**
     * Marker for the host ambient authority. No user-facing methods.
     */
    class StephpCapStdAmbientAuthority {}

    /**
     * Capability-scoped directory handle. All paths are relative to the
     * directory this handle represents; `..` traversal is rejected.
     */
    class StephpCapStdDir {
        /**
         * Returns the names of the entries in this directory.
         * @throws \Exception on I/O error
         */
        public function entries(): \StephpCapStdEntries {}

        /**
         * Returns the names of the entries in a subdirectory.
         * @throws \Exception on I/O error
         */
        public function read_dir(string $path): \StephpCapStdEntries {}

        /**
         * Opens a subdirectory, producing a new sandboxed handle.
         * @throws \Exception on I/O error
         */
        public function open_dir(string $path): \StephpCapStdDir {}

        /**
         * Opens a file in read-only mode.
         * @throws \Exception on I/O error
         */
        public function open(string $path): \StephpCapStdFile {}

        /**
         * Opens a file with custom options.
         * @throws \Exception on I/O error
         */
        public function open_with(string $path, \StephpCapStdOpenOptions $options): \StephpCapStdFile {}

        /**
         * Opens a file in write-only mode, creating or truncating it.
         * @throws \Exception on I/O error
         */
        public function create(string $path): \StephpCapStdFile {}

        /**
         * Creates a new, empty directory.
         * @throws \Exception on I/O error
         */
        public function create_dir(string $path): void {}

        /**
         * Recursively creates a directory and any missing parent.
         * @throws \Exception on I/O error
         */
        public function create_dir_all(string $path): void {}

        /**
         * Copies a file into another directory. Returns the number of
         * bytes copied. Permission bits are copied as well.
         * @throws \Exception on I/O error
         */
        public function copy(string $from, \StephpCapStdDir $to_dir, string $to): int {}

        /**
         * Renames or moves a file or directory into another directory.
         * @throws \Exception on I/O error
         */
        public function rename(string $from, \StephpCapStdDir $to_dir, string $to): void {}

        /**
         * Returns the metadata of the directory handle itself.
         * @throws \Exception on I/O error
         */
        public function dir_metadata(): \StephpCapStdMetadata {}

        /**
         * Returns the canonical form of a path, resolved relative to this
         * directory (the result is relative, not absolute).
         * @throws \Exception on I/O error
         */
        public function canonicalize(string $path): string {}

        /**
         * Reads the entire contents of a file. The returned string is
         * binary-safe (may contain raw bytes).
         * @throws \Exception on I/O error
         */
        public function read(string $path): string {}

        /**
         * Reads the entire contents of a file as UTF-8 text.
         * @throws \Exception on I/O error or invalid UTF-8
         */
        public function read_to_string(string $path): string {}

        /**
         * Writes a string as the entire contents of a file (binary-safe).
         * @throws \Exception on I/O error
         */
        public function write(string $path, string $data): void {}

        /**
         * Removes an empty directory.
         * @throws \Exception on I/O error
         */
        public function remove_dir(string $path): void {}

        /**
         * Removes a directory and all its contents. Use carefully.
         * @throws \Exception on I/O error
         */
        public function remove_dir_all(string $path): void {}

        /**
         * Removes a file.
         * @throws \Exception on I/O error
         */
        public function remove_file(string $path): void {}

        /**
         * Returns whether the path exists and points to a regular file.
         */
        public function is_file(string $path): bool {}

        /**
         * Returns whether the path exists and points to a directory.
         */
        public function is_dir(string $path): bool {}

        /**
         * Returns whether the path exists.
         */
        public function exists(string $path): bool {}

        /**
         * Creates a hard link into another directory.
         * @throws \Exception on I/O error
         */
        public function hard_link(string $src, \StephpCapStdDir $dst_dir, string $dst): void {}

        /**
         * Returns the metadata of the entry at the given path (symlinks
         * are followed).
         * @throws \Exception on I/O error
         */
        public function metadata(string $path): \StephpCapStdMetadata {}

        /**
         * Returns the target of a symbolic link. Absolute targets are
         * considered an error.
         * @throws \Exception on I/O error
         */
        public function read_link(string $path): string {}

        /**
         * Returns the metadata of the entry at the given path without
         * following symlinks.
         * @throws \Exception on I/O error
         */
        public function symlink_metadata(string $path): \StephpCapStdMetadata {}

        /**
         * Changes the permissions of an entry. Unix only.
         * @throws \Exception on I/O error or on non-Unix platforms
         */
        public function set_permissions(string $path, \StephpCapStdPermissions $perm): void {}

        /**
         * Creates a symbolic link. The target must be a relative path;
         * Unix only.
         * @throws \Exception on I/O error or on non-Unix platforms
         */
        public function symlink(string $original, string $link): void {}

        /**
         * Duplicates the directory handle, sharing the same underlying
         * file descriptor.
         * @throws \Exception on I/O error
         */
        public function try_clone(): \StephpCapStdDir {}
    }

    /**
     * Iterator over directory entry names, produced by
     * StephpCapStdDir::entries() and read_dir(). Also countable.
     */
    class StephpCapStdEntries implements \Countable, \Iterator {
        /**
         * Returns the number of entries.
         */
        public function count(): int {}

        /**
         * Restarts the iteration (required by \Iterator).
         */
        public function rewind(): void {}

        /**
         * Returns the current entry name, or null past the end.
         */
        public function current(): ?string {}

        /**
         * Returns the current iteration index.
         */
        public function key(): int {}

        /**
         * Advances the iteration (required by \Iterator).
         */
        public function next(): void {}

        /**
         * Returns whether the current position is valid.
         */
        public function valid(): bool {}
    }

    /**
     * Filesystem metadata of a file, directory, or symlink.
     */
    class StephpCapStdMetadata {
        /**
         * Returns the file type of the entry.
         */
        public function file_type(): \StephpCapStdFileType {}

        /**
         * Returns whether the entry is a directory.
         */
        public function is_dir(): bool {}

        /**
         * Returns whether the entry is a regular file.
         */
        public function is_file(): bool {}

        /**
         * Returns whether the entry is a symbolic link.
         */
        public function is_symlink(): bool {}

        /**
         * Returns the size of the entry in bytes.
         */
        public function len(): int {}

        /**
         * Returns the size of the entry in bytes (alias of len()).
         */
        public function size(): int {}

        /**
         * Returns the permissions of the entry.
         */
        public function permissions(): \StephpCapStdPermissions {}

        /**
         * Returns the last modification time.
         * @throws \Exception when the platform does not provide it
         */
        public function modified(): \StephpCapStdSystemTime {}

        /**
         * Returns the last access time.
         * @throws \Exception when the platform does not provide it
         */
        public function accessed(): \StephpCapStdSystemTime {}

        /**
         * Returns the creation time.
         * @throws \Exception when the platform does not provide it
         */
        public function created(): \StephpCapStdSystemTime {}

        /**
         * Returns the device ID holding the entry. Unix only.
         */
        public function dev(): int {}

        /**
         * Returns the inode number. Unix only.
         */
        public function ino(): int {}

        /**
         * Returns the permission mode bits. Unix only.
         */
        public function mode(): int {}

        /**
         * Returns the number of hard links. Unix only.
         */
        public function nlink(): int {}

        /**
         * Returns the owner user ID. Unix only.
         */
        public function uid(): int {}

        /**
         * Returns the owner group ID. Unix only.
         */
        public function gid(): int {}

        /**
         * Returns the device ID of the entry, if it is a device. Unix only.
         */
        public function rdev(): int {}

        /**
         * Returns the last access time, in whole seconds. Unix only.
         */
        public function atime(): int {}

        /**
         * Returns the last access time, nanoseconds part. Unix only.
         */
        public function atime_nsec(): int {}

        /**
         * Returns the last modification time, in whole seconds. Unix only.
         */
        public function mtime(): int {}

        /**
         * Returns the last modification time, nanoseconds part. Unix only.
         */
        public function mtime_nsec(): int {}

        /**
         * Returns the last status change time, in whole seconds. Unix only.
         */
        public function ctime(): int {}

        /**
         * Returns the last status change time, nanoseconds part. Unix only.
         */
        public function ctime_nsec(): int {}

        /**
         * Returns the preferred block size for I/O. Unix only.
         */
        public function blksize(): int {}

        /**
         * Returns the number of blocks allocated. Unix only.
         */
        public function blocks(): int {}
    }

    /**
     * Capability-scoped file handle, produced by the open/create methods
     * of StephpCapStdDir. Holds a shared cursor; concurrent use is safe
     * but not transactional.
     */
    class StephpCapStdFile {
        /**
         * Flushes OS buffers, syncing data and metadata to disk.
         * @throws \Exception on I/O error
         */
        public function sync_all(): void {}

        /**
         * Flushes OS buffers, syncing data but not necessarily metadata.
         * Often faster than sync_all().
         * @throws \Exception on I/O error
         */
        public function sync_data(): void {}

        /**
         * Truncates or extends the file to the given size.
         * @throws \Exception on I/O error
         */
        public function set_len(int $size): void {}

        /**
         * Returns the metadata of the open file.
         * @throws \Exception on I/O error
         */
        public function metadata(): \StephpCapStdMetadata {}

        /**
         * Changes the permissions of the open file.
         * @throws \Exception on I/O error
         */
        public function set_permissions(\StephpCapStdPermissions $permissions): void {}

        /**
         * Reads up to the given number of bytes from the current cursor.
         * The returned string is binary-safe. Returns an empty string at
         * end of file.
         * @throws \Exception on I/O error
         */
        public function read(int $length): string {}

        /**
         * Reads from the current cursor to the end of the file.
         * The returned string is binary-safe.
         * @throws \Exception on I/O error
         */
        public function read_to_end(): string {}

        /**
         * Reads from the current cursor to the end of the file as UTF-8
         * text.
         * @throws \Exception on I/O error or invalid UTF-8
         */
        public function read_to_string(): string {}

        /**
         * Writes binary-safe data at the current cursor. Returns the
         * number of bytes written.
         * @throws \Exception on I/O error
         */
        public function write(string $data): int {}

        /**
         * No-op for consistency with PHP streams; writes are not buffered
         * in userspace.
         * @throws \Exception on I/O error
         */
        public function flush(): void {}

        /**
         * Rewinds the cursor to the start of the file.
         * @throws \Exception on I/O error
         */
        public function rewind(): void {}

        /**
         * Returns the current cursor position.
         * @throws \Exception on I/O error
         */
        public function stream_position(): int {}

        /**
         * Moves the cursor by the given signed offset, relative to the
         * current position.
         * @throws \Exception on I/O error
         */
        public function seek_relative(int $offset): void {}

        /**
         * Moves the cursor to an absolute or relative position. $whence is
         * one of the STEHP_CAP_STD_SEEK_SET / _SEEK_CUR / _SEEK_END
         * constants (0, 1, 2). Returns the resulting position.
         * @throws \Exception on I/O error or invalid $whence
         */
        public function seek(int $offset, int $whence): int {}

        /**
         * Returns the size of the file.
         * @throws \Exception on I/O error
         */
        public function stream_len(): int {}

        /**
         * Duplicates the file handle, sharing the same file description
         * (including the cursor).
         * @throws \Exception on I/O error
         */
        public function try_clone(): \StephpCapStdFile {}

        /**
         * Sets the last access and/or modification times of the open
         * file. Pass null to leave a timestamp unchanged.
         * @throws \Exception on I/O error
         */
        public function set_times(?\StephpCapStdSystemTime $atime = null, ?\StephpCapStdSystemTime $mtime = null): void {}
    }

    /**
     * File type of a filesystem entry.
     */
    class StephpCapStdFileType {
        /**
         * Returns whether the entry is a directory.
         */
        public function is_dir(): bool {}

        /**
         * Returns whether the entry is a regular file.
         */
        public function is_file(): bool {}

        /**
         * Returns whether the entry is a symbolic link.
         */
        public function is_symlink(): bool {}
    }

    /**
     * Point in time, used for timestamp manipulation.
     */
    class StephpCapStdSystemTime {
        /**
         * Builds a time from a Unix timestamp, in whole seconds.
         */
        public static function from_unix_timestamp(int $seconds): \StephpCapStdSystemTime {}

        /**
         * Returns the time as a Unix timestamp, in whole seconds. Times
         * before the Unix epoch yield 0.
         */
        public function to_unix_timestamp_seconds_utc(): int {}
    }

    /**
     * File or directory permissions.
     */
    class StephpCapStdPermissions {
        /**
         * Builds a permissions object from Unix mode bits
         * (e.g. 0644). Unix only.
         * @throws \Exception on non-Unix platforms
         */
        public static function new(int $mode): \StephpCapStdPermissions {}

        /**
         * Returns whether the entry is read-only.
         */
        public function readonly(): bool {}

        /**
         * Marks the entry read-only (removes write bits on Unix).
         */
        public function set_readonly(bool $readonly): void {}

        /**
         * Returns the Unix mode bits. Unix only.
         */
        public function mode(): int {}

        /**
         * Sets the Unix mode bits. Unix only.
         */
        public function set_mode(int $mode): void {}
    }

    /**
     * Options for opening files, consumed by
     * StephpCapStdDir::open_with(). Created via the static constructor.
     */
    class StephpCapStdOpenOptions {
        /**
         * Builds an empty set of options.
         */
        public static function new(): \StephpCapStdOpenOptions {}

        /**
         * Enables read access.
         */
        public function read(bool $read): void {}

        /**
         * Enables write access.
         */
        public function write(bool $enable): void {}

        /**
         * Opens in append mode (implies write).
         */
        public function append(bool $enable): void {}

        /**
         * Truncates the file to zero length on open.
         */
        public function truncate(bool $enable): void {}

        /**
         * Creates the file if it does not exist.
         */
        public function create(bool $enable): void {}

        /**
         * Creates a new file; fails if it already exists.
         */
        public function create_new(bool $enable): void {}

        /**
         * Sets Unix mode bits used when creating the file (e.g. 0644).
         * Unix only.
         * @throws \Exception on non-Unix platforms
         */
        public function mode(int $mode): void {}
    }
}
