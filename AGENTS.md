# AGENTS.md

## Project Overview

`stephp-cap-std` is a PHP extension written in Rust (via [ext-php-rs](https://github.com/extphprs/ext-php-rs)) exposing [cap-std](https://github.com/bytecodealliance/cap-std): capability-based filesystem access for PHP. Once a directory handle is obtained via `stephp_cap_std_open_ambient_dir()`, all operations are sandboxed to that directory tree — path traversal (`../`) is structurally impossible. Linux/Unix is the primary target.

## Essential Commands

```bash
# Build (requires PHP 8+ dev headers and Clang for ext-php-rs binding generation)
cargo build --release

# Run the test suite (no test framework, plain PHP harness)
cd php/ && php -d extension=../target/release/libstephp_cap_std.so test.php
# Success looks like: "🥳 🎉  SUCCESS!" — any ❌ KO line means failure
```

There are no Rust unit tests; all verification happens through the PHP harness. `cargo test` compiles but has nothing to run. `cargo fmt` was used historically; nothing enforces it (no CI, no Makefile).

## Architecture

`src/` is one file per exposed PHP class. Data flow: PHP starts with `stephp_cap_std_ambient_authority()` → `stephp_cap_std_open_ambient_dir($auth, $path)` → everything else goes through `StephpCapStdDir`/`StephpCapStdFile` handles, with paths always relative to a handle.

| PHP class | Rust file | Wraps |
|---|---|---|
| `StephpCapStdAmbientAuthority` | `src/lib.rs` | `cap_std::AmbientAuthority` |
| `StephpCapStdDir` | `src/dir.rs` | `cap_std::fs::Dir` — main entry point |
| `StephpCapStdFile` | `src/file.rs` | `cap_std::fs::File` |
| `StephpCapStdEntries` | `src/entries.rs` | Vec of entry names; implements PHP `Iterator` + `Countable` |
| `StephpCapStdMetadata` | `src/metadata.rs` | `cap_std::fs::Metadata` incl. Unix `MetadataExt` |
| `StephpCapStdOpenOptions` | `src/openoptions.rs` | `cap_std::fs::OpenOptions` |
| `StephpCapStdPermissions` | `src/permissions.rs` | `cap_std::fs::Permissions` |
| `StephpCapStdFileType` | `src/filetype.rs` | `cap_std::fs::FileType` |
| `StephpCapStdSystemTime` | `src/systemtime.rs` | `cap_std::time::SystemTime` |

### Adding a class/method — full checklist

A new API element must be reflected in **five** places or it drifts:

1. `src/*.rs` — implementation, with `#[php(name = "snake_case")]` on each method
2. `src/lib.rs` `get_module()` — new classes need `.class::<T>()`; new global functions need `wrap_function!` (methods inside an already-registered class are picked up automatically)
3. `stephp-cap-std.stubs.php` — IDE stubs (already drifted, see Gotchas)
4. `README.md` "Available API" section
5. `php/test.php` — add both the `include_once` and the call in the `try` block, plus the new test file under `php/dir/` or `php/file/`

### Conventions in `src/`

- **Errors**: fallible methods return `Result<T, String>`; ext-php-rs turns `Err` into a PHP exception. Errors are stringified via `.map_err(|e| e.to_string())`; mutex poisoning yields `"Mutex lock error"`. Don't introduce custom exception types.
- **ZTS safety**: shared/mutable handles (`File`, `OpenOptions`, `Permissions`, `Entries`) wrap their inner state in `std::sync::Mutex` and lock per method; `Dir`, `Metadata`, `FileType`, `SystemTime` don't. Follow the per-file pattern.
- **Platform-specific code**: put `#[cfg(unix)]` **inside the method body**, with a `#[cfg(not(unix))]` fallback returning `Err("... is only available on Unix systems")` — not on the whole `#[php_impl]` method, which caused duplicate class registration problems (fixed in commit 8cc6228). Exception: `StephpCapStdPermissions::new()` is wholly `#[cfg(unix)]`.
- Every `src/*.rs` starts with `#![cfg_attr(windows, feature(abi_vectorcall))]` (nightly requirement on Windows only).
- **Binary data**: reads return `Binary<u8>`, writes take `BinarySlice<u8>` (both binary-safe PHP strings).
- Rust and PHP method names mirror cap-std's Rust names in snake_case — no camelCase.

## PHP tests (`php/`)

Plain harness, no PHPUnit. `php/common.php` provides `ok()`, `ko()`, `warning()`, `result()` (any `ko()` call = failure) and `recursive_rmdir()`.

Test file pattern:
- namespace `tests\dir` or `tests\file`, one function per file taking the sandbox `string $root` (created by `test.php` as a unique dir under the system temp dir, cleaned up in `finally`)
- tests verify extension effects using native PHP filesystem functions (`file_get_contents`, `md5_file`, ...) alongside the extension — that is the point
- binary (non-UTF-8) coverage uses `reference.png` sitting next to the test file, compared via checksums
- security behavior has dedicated tests (`traversal_blocked.php`, `symlink_safety.php`) asserting that escapes **throw**

Tests can only be run through `php/test.php`; individual test files just define a namespaced function and output nothing when run standalone.

## Gotchas

- **The README usage example is wrong**: it shows `new StephpCapStdOpenOptions()`, but ext-php-rs classes **cannot be instantiated with `new`** (throws "You cannot instantiate this class from PHP."). Use static constructors: `\StephpCapStdOpenOptions::new()`, `\StephpCapStdPermissions::new($mode)`, `StephpCapStdSystemTime::from_unix_timestamp($ts)`. The test files are the authoritative usage examples.
- **PHP stat cache**: extension writes bypass PHP streams, so `file_exists()`/`is_file()` may return stale results. Call `clearstatcache()` or use the extension's own `exists()`/`is_file()`/`is_dir()`.
- **Stubs drift**: `stephp-cap-std.stubs.php` says `Dir::read()`/`File::read()` return `string` (they return raw bytes) and lists `__construct()` on classes that have no usable constructor. Treat stubs and README as docs only; `src/` is the source of truth.
- **`.so` path confusion**: the header comment in `php/test.php` points to `../target/debug/...`, the README to `../target/release/...`. Both work; verify against release.
- **`Dir::set_times()` is deliberately missing**: noted in `NOTES-TMP.txt` as "A ECHOUE, A REFAIRE" (failed, to redo). `File::set_times()` exists. Other known gaps are listed there too (`open_parent`, `DirBuilder`, `advice()`, `read_at`/`write_at`).
- **French working notes**: `NOTES-TMP.txt` and `.cursor/plans/*.plan.md` are the roadmap/gap analysis, written in French.
- **Version is tracked in two places**: `Cargo.toml` `version` and the README header ("Version 0.5.0") — bump both together.
- **Naming history**: project was once `php-cap-std`; test temp dirs still use the `php-cap-std-tests-` prefix and README clone instructions say `cd php-cap-std`. The current name `stephp-cap-std` is what matters.
- Dependabot is active and `Cargo.lock` is committed; keep lockfile changes in separate commits.
