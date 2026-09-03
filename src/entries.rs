#![cfg_attr(windows, feature(abi_vectorcall))]

use ext_php_rs::{prelude::*, zend::ce};
use std::sync::Mutex;

#[php_class]
#[php(implements(ce = ce::countable, stub = "\\Countable"))]
#[php(implements(ce = ce::iterator, stub = "\\Iterator"))]
pub struct StephpCapStdEntries {
    pub inner: Mutex<EntriesInternal>,
}

pub struct EntriesInternal {
    entries: Vec<String>,
    current_index: usize,
}

#[php_impl]
impl StephpCapStdEntries {
    pub fn count(&self) -> usize {
        self.inner
            .lock()
            .map(|inner| inner.entries.len())
            .unwrap_or(0)
    }

    pub fn rewind(&self) {
        if let Ok(mut inner) = self.inner.lock() {
            inner.current_index = 0;
        }
    }

    pub fn current(&self) -> Option<String> {
        let inner = self.inner.lock().ok()?;
        inner.entries.get(inner.current_index).cloned()
    }

    pub fn key(&self) -> usize {
        self.inner
            .lock()
            .map(|inner| inner.current_index)
            .unwrap_or(0)
    }

    pub fn next(&self) {
        if let Ok(mut inner) = self.inner.lock() {
            inner.current_index += 1;
        }
    }

    pub fn valid(&self) -> bool {
        self.inner
            .lock()
            .map(|inner| inner.current_index < inner.entries.len())
            .unwrap_or(false)
    }
}

// Internal constructor, intentionally not exposed to PHP: entries objects
// are only produced by the extension (Dir::entries / Dir::read_dir).
impl StephpCapStdEntries {
    pub fn new(entries: Vec<String>) -> Self {
        Self {
            inner: Mutex::new(EntriesInternal {
                entries,
                current_index: 0,
            }),
        }
    }
}
