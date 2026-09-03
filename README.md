# stephp-cap-std

**Version 0.6.0**

`stephp-cap-std` is an experimental PHP extension written in Rust. It provides bindings to the [cap-std](https://github.com/bytecodealliance/cap-std) crate, offering a **capability-based security** approach for filesystem access. This project is made possible thanks to the [ext-php-rs](https://github.com/extphprs/ext-php-rs) project, which provides the tools to build PHP extensions with Rust.

The primary goal is to enable robust **sandboxing** within PHP scripts. Unlike native PHP filesystem functions, this extension ensures that once a directory handle is obtained, it is impossible to access files outside of that directory tree (e.g., via `../../`), providing strong protection against directory traversal attacks.

## 🤖 AI Statement

This project leverages Artificial Intelligence. This `README.md` file, as well as significant portions of the source code and the test suite, have been generated or refined by AI models.

## Prerequisites

*   **Linux** (or a compatible Unix system)
*   **Rust** (via `rustup`, **2024 edition** as specified in `Cargo.toml`)
*   **PHP** (version 8.0+, with development headers installed, e.g., `php-dev` or `php-devel`)
*   **Clang** (required for binding generation by `ext-php-rs`)

## Compilation and Installation

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd stephp-cap-std
    ```

2.  **Build the project:**
    Use Cargo to build the dynamic library.
    ```bash
    cargo build --release
    ```
    The generated artifact will be located at `target/release/libstephp_cap_std.so`.

3.  **Enable the extension:**

    **Option A: Command line (for testing)**
    ```bash
    php -d extension=target/release/libstephp_cap_std.so your_script.php
    ```

    **Option B: Permanent configuration**
    Copy the `.so` file to your PHP extensions directory and add the following line to your `php.ini`:
    ```ini
    extension=libstephp_cap_std.so
    ```

## Usage Example

Capability-based security means you don't use global paths. You start with an "Ambient Authority" to open a specific entry-point directory, and then work exclusively through that handle.

```php
<?php

// 1. Obtain the ambient authority
$auth = stephp_cap_std_ambient_authority();

// 2. Open a root directory for the sandbox
try {
    $dir = stephp_cap_std_open_ambient_dir($auth, '/tmp/sandbox');
    
    // Write a file with specific options
    $options = StephpCapStdOpenOptions::new();
    $options->write(true);
    $options->create(true);
    $options->truncate(true);
    
    $file = $dir->open_with("data.log", $options);
    $file->write("Log entry at " . date('Y-m-d H:i:s') . "\n");
    $file->flush();
    
    // Operations between directories (Capability approach)
    $backupDir = $dir->open_dir("backups");
    $dir->copy("data.log", $backupDir, "data.log.bak");
    
    // Metadata and Time
    $meta = $dir->metadata("data.log");
    $mtime = $meta->modified();
    echo "Last modified: " . $mtime->to_unix_timestamp_seconds_utc() . "\n";
    
    // Safe listing
    foreach ($dir->read_dir(".") as $name) {
        echo "Found: $name\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

> **Note:** Extension classes cannot be instantiated with `new` from PHP. Use their static constructors instead: `StephpCapStdOpenOptions::new()`, `StephpCapStdPermissions::new($mode)`, `StephpCapStdSystemTime::from_unix_timestamp($seconds)`. The test suite in `php/` doubles as a usage reference.

## Available API

As this extension provides direct bindings to the Rust [cap-std](https://docs.rs/cap-std/) library, it is highly recommended to consult its official documentation to understand the detailed behavior of each method and their respective parameters.

### Global Functions
- `stephp_cap_std_ambient_authority(): StephpCapStdAmbientAuthority`
- `stephp_cap_std_open_ambient_dir(StephpCapStdAmbientAuthority $auth, string $path): StephpCapStdDir`

### `StephpCapStdDir` (Directory Handle)
Main entry point for scoped filesystem operations.
- **Files**: `open(string $path)`, `open_with(string $path, StephpCapStdOpenOptions $opts)`, `create(string $path)`, `read(string $path)`, `read_to_string(string $path)`, `write(string $path, string|Binary $data)`, `remove_file(string $path)`
- **Directories**: `open_dir(string $path)`, `create_dir(string $path)`, `create_dir_all(string $path)`, `create_dir_with(string $path, StephpCapStdDirBuilder $builder)`, `remove_dir(string $path)`, `remove_dir_all(string $path)`, `read_dir(string $path): StephpCapStdEntries`
- **Inter-Directory**: `copy(string $from, StephpCapStdDir $to_dir, string $to)`, `rename(string $from, StephpCapStdDir $to_dir, string $to)`, `hard_link(string $src, StephpCapStdDir $dst_dir, string $dst)`
- **Metadata/Info**: `exists(string $path)`, `try_exists(string $path)`, `is_file(string $path)`, `is_dir(string $path)`, `metadata(string $path)`, `symlink_metadata(string $path)`, `dir_metadata()`, `canonicalize(string $path)`, `read_link(string $path)`, `read_link_contents(string $path)`, `try_clone()`
- **Timestamps & System**: `set_own_times(?StephpCapStdSystemTime $atime, ?StephpCapStdSystemTime $mtime)`, `set_times(string $path, ?StephpCapStdSystemTime $atime, ?StephpCapStdSystemTime $mtime)`, `set_permissions(string $path, StephpCapStdPermissions $p)`, `symlink(string $orig, string $link)`, `symlink_contents(string $orig, string $link)`

### `StephpCapStdFile` (File Handle)
- **Constants**: `StephpCapStdFile::SEEK_SET`, `StephpCapStdFile::SEEK_CUR`, `StephpCapStdFile::SEEK_END`
- **IO**: `read(int $len)`, `read_to_end()`, `read_to_string()`, `write(string $data)`, `flush()`
- **Offset IO (Unix)**: `read_at(int $len, int $offset)`, `read_exact_at(int $len, int $offset)`, `write_at(string $data, int $offset)`, `write_all_at(string $data, int $offset)`
- **Position**: `seek(int $offset, int $whence)`, `seek_relative(int $offset)`, `rewind()`, `stream_position()`, `stream_len()`
- **Management**: `sync_all()`, `sync_data()`, `set_len(int $size)`, `metadata()`, `set_permissions(StephpCapStdPermissions $p)`, `try_clone()`, `set_times(?StephpCapStdSystemTime $atime, ?StephpCapStdSystemTime $mtime)`

### `StephpCapStdOpenOptions`
- `new()` (static), `read(bool)`, `write(bool)`, `append(bool)`, `truncate(bool)`, `create(bool)`, `create_new(bool)`, `mode(int $mode)` (Unix only), `custom_flags(int $flags)` (Unix only)

### `StephpCapStdDirBuilder`
- `new()` (static), `recursive(bool)`, `mode(int $mode)` (Unix only)

### `StephpCapStdMetadata`
- `is_dir()`, `is_file()`, `is_symlink()`, `len()`, `size()`, `file_type()`, `permissions()`
- `modified()`, `accessed()`, `created()` (Returns `StephpCapStdSystemTime`)
- Linux/Unix specific: `dev()`, `ino()`, `mode()`, `uid()`, `gid()`, `nlink()`, `rdev()`, `blksize()`, `blocks()`, `atime()`, `mtime()`, `ctime()`, `atime_nsec()`, `mtime_nsec()`, `ctime_nsec()`

### `StephpCapStdEntries`
Implements `Iterator` and `Countable`. Returned by `read_dir()`.
- `count()`, `rewind()`, `current()`, `key()`, `next()`, `valid()`

### Supporting Classes
- `StephpCapStdPermissions`: `new(int $mode)` (static, Unix), `readonly()`, `set_readonly(bool)`, `mode()`, `set_mode(int)`
- `StephpCapStdSystemTime`: `from_unix_timestamp(int $seconds)` (static), `to_unix_timestamp_seconds_utc()`
- `StephpCapStdFileType`: `is_dir()`, `is_file()`, `is_symlink()`

## 🧪 Testing

The project includes an auto-discovering test runner located in `php/test.php`.

To run the full test suite:
```bash
make test
# Or directly via PHP:
cd php/
php -d extension=../target/release/libstephp_cap_std.so test.php
```

To run a single test or a subset of tests using a filter pattern:
```bash
cd php/
php -d extension=../target/release/libstephp_cap_std.so test.php --filter=dir/write
```

## Limitations and Warnings

### ⚠️ PHP Stat Cache (`clearstatcache`)
This extension bypasses PHP's standard streams. PHP's internal cache for functions like `file_exists()` might not reflect changes made via this extension.
**Solution**: Use the methods on the `$dir` object (e.g., `$dir->exists()`) or call `clearstatcache()` in PHP.

### Performance
FFI boundary crossings between PHP and Rust have a minor overhead. For security-critical sandboxing, this is a negligible trade-off.

## License
This project is licensed under the MIT License.
