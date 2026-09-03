.PHONY: all build build-debug test lint fmt fmt-check check clean

all: build

build:
	cargo build --release

build-debug:
	cargo build

test: build
	cd php && php -d extension=../target/release/libstephp_cap_std.so test.php

lint:
	cargo clippy -- -D warnings

fmt:
	cargo fmt

fmt-check:
	cargo fmt --check

check: fmt-check lint test

clean:
	cargo clean
