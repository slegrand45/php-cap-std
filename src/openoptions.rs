#![cfg_attr(windows, feature(abi_vectorcall))]

#[cfg(unix)]
use cap_std::fs::OpenOptionsExt;
use ext_php_rs::prelude::*;
use std::sync::Mutex;

#[php_class]
pub struct StephpCapStdOpenOptions {
    pub inner: Mutex<cap_std::fs::OpenOptions>,
}

#[php_impl]
impl StephpCapStdOpenOptions {
    pub fn new() -> Self {
        Self {
            inner: Mutex::new(cap_std::fs::OpenOptions::new()),
        }
    }

    #[php(name = "read")]
    pub fn read(&mut self, read: bool) {
        if let Ok(mut options) = self.inner.lock() {
            options.read(read);
        }
    }

    #[php(name = "write")]
    pub fn write(&mut self, enable: bool) {
        if let Ok(mut options) = self.inner.lock() {
            options.write(enable);
        }
    }

    #[php(name = "append")]
    pub fn append(&mut self, enable: bool) {
        if let Ok(mut options) = self.inner.lock() {
            options.append(enable);
        }
    }

    #[php(name = "truncate")]
    pub fn truncate(&mut self, enable: bool) {
        if let Ok(mut options) = self.inner.lock() {
            options.truncate(enable);
        }
    }

    #[php(name = "create")]
    pub fn create(&mut self, enable: bool) {
        if let Ok(mut options) = self.inner.lock() {
            options.create(enable);
        }
    }

    #[php(name = "create_new")]
    pub fn create_new(&mut self, enable: bool) {
        if let Ok(mut options) = self.inner.lock() {
            options.create_new(enable);
        }
    }

    #[php(name = "mode")]
    pub fn mode(&mut self, mode: u32) -> Result<(), String> {
        #[cfg(unix)]
        {
            let mut options = self
                .inner
                .lock()
                .map_err(|_| "Mutex lock error".to_string())?;
            options.mode(mode);
            Ok(())
        }
        #[cfg(not(unix))]
        {
            let _ = mode;
            Err("mode is only available on Unix systems".to_string())
        }
    }
}
