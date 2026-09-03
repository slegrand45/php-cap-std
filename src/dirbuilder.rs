#![cfg_attr(windows, feature(abi_vectorcall))]

#[cfg(unix)]
use cap_std::fs::DirBuilderExt;
use ext_php_rs::prelude::*;
use std::sync::Mutex;

#[php_class]
pub struct StephpCapStdDirBuilder {
    pub inner: Mutex<cap_std::fs::DirBuilder>,
}

#[php_impl]
impl StephpCapStdDirBuilder {
    pub fn new() -> Self {
        Self {
            inner: Mutex::new(cap_std::fs::DirBuilder::new()),
        }
    }

    #[php(name = "recursive")]
    pub fn recursive(&self, recursive: bool) -> Result<(), String> {
        let mut builder = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        builder.recursive(recursive);
        Ok(())
    }

    #[php(name = "mode")]
    pub fn mode(&self, mode: u32) -> Result<(), String> {
        #[cfg(unix)]
        {
            let mut builder = self
                .inner
                .lock()
                .map_err(|_| "Mutex lock error".to_string())?;
            builder.mode(mode);
            Ok(())
        }
        #[cfg(not(unix))]
        {
            let _ = mode;
            Err("mode is only available on Unix systems".to_string())
        }
    }
}
