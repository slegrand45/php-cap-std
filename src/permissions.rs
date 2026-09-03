#![cfg_attr(windows, feature(abi_vectorcall))]

#[cfg(unix)]
use cap_std::fs::PermissionsExt;

use ext_php_rs::prelude::*;
use std::sync::Mutex;

#[php_class]
pub struct StephpCapStdPermissions {
    pub inner: Mutex<cap_std::fs::Permissions>,
}

#[php_impl]
impl StephpCapStdPermissions {
    pub fn new(mode: u32) -> Result<Self, String> {
        #[cfg(unix)]
        {
            Ok(Self {
                inner: Mutex::new(cap_std::fs::Permissions::from_mode(mode)),
            })
        }
        #[cfg(not(unix))]
        {
            let _ = mode;
            Err("Permissions::new is only available on Unix systems".to_string())
        }
    }

    #[php(name = "readonly")]
    pub fn readonly(&self) -> bool {
        self.inner
            .lock()
            .map(|inner| inner.readonly())
            .unwrap_or(false)
    }

    #[php(name = "set_readonly")]
    pub fn set_readonly(&self, readonly: bool) {
        if let Ok(mut inner) = self.inner.lock() {
            inner.set_readonly(readonly)
        }
    }

    #[php(name = "mode")]
    pub fn mode(&self) -> u32 {
        #[cfg(unix)]
        {
            self.inner.lock().map(|inner| inner.mode()).unwrap_or(0)
        }
        #[cfg(not(unix))]
        0
    }

    #[php(name = "set_mode")]
    pub fn set_mode(&self, mode: u32) {
        #[cfg(unix)]
        {
            if let Ok(mut inner) = self.inner.lock() {
                inner.set_mode(mode)
            }
        }
    }
}
