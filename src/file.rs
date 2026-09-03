#![cfg_attr(windows, feature(abi_vectorcall))]

#[cfg(unix)]
use cap_std::fs::FileExt;

use crate::metadata;
use crate::permissions::StephpCapStdPermissions;
use ext_php_rs::binary::Binary;
use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::prelude::*;
use fs_set_times::{SetTimes, SystemTimeSpec};
use std::io::Read;
use std::io::Seek;
use std::io::SeekFrom;
use std::io::Write;
use std::sync::Mutex;

#[php_class]
pub struct StephpCapStdFile {
    pub inner: Mutex<cap_std::fs::File>,
}

#[php_impl]
impl StephpCapStdFile {
    pub const SEEK_SET: i32 = 0;
    pub const SEEK_CUR: i32 = 1;
    pub const SEEK_END: i32 = 2;

    #[php(name = "sync_all")]
    pub fn sync_all(&self) -> Result<(), String> {
        let file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.sync_all().map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "sync_data")]
    pub fn sync_data(&self) -> Result<(), String> {
        let file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.sync_data().map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "set_len")]
    pub fn set_len(&self, size: u64) -> Result<(), String> {
        let file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.set_len(size).map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "metadata")]
    pub fn metadata(&self) -> Result<metadata::StephpCapStdMetadata, String> {
        let file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let metadata = file.metadata().map_err(|e| e.to_string())?;
        Ok(metadata::StephpCapStdMetadata { inner: metadata })
    }

    #[php(name = "set_permissions")]
    pub fn set_permissions(&self, permissions: &StephpCapStdPermissions) -> Result<(), String> {
        let permissions = permissions
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.set_permissions(permissions.clone())
            .map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "read")]
    pub fn read(&self, length: usize) -> Result<Binary<u8>, String> {
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let mut data = vec![0u8; length];
        let bytes_read = file.read(&mut data).map_err(|e| e.to_string())?;
        data.truncate(bytes_read);
        Ok(Binary::from(data))
    }

    #[php(name = "read_to_end")]
    pub fn read_to_end(&self) -> Result<Binary<u8>, String> {
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let mut data = Vec::new();
        let bytes_read = file.read_to_end(&mut data).map_err(|e| e.to_string())?;
        data.truncate(bytes_read);
        Ok(Binary::from(data))
    }

    #[php(name = "read_to_string")]
    pub fn read_to_string(&self) -> Result<String, String> {
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let mut data = String::new();
        let bytes_read = file.read_to_string(&mut data).map_err(|e| e.to_string())?;
        data.truncate(bytes_read);
        Ok(data)
    }

    #[php(name = "read_at")]
    pub fn read_at(&self, length: usize, offset: u64) -> Result<Binary<u8>, String> {
        #[cfg(unix)]
        {
            let file = self
                .inner
                .lock()
                .map_err(|_| "Mutex lock error".to_string())?;
            let mut data = vec![0u8; length];
            let bytes_read = file.read_at(&mut data, offset).map_err(|e| e.to_string())?;
            data.truncate(bytes_read);
            Ok(Binary::from(data))
        }
        #[cfg(not(unix))]
        {
            let _ = (length, offset);
            Err("read_at is only available on Unix systems".to_string())
        }
    }

    #[php(name = "read_exact_at")]
    pub fn read_exact_at(&self, length: usize, offset: u64) -> Result<Binary<u8>, String> {
        #[cfg(unix)]
        {
            let file = self
                .inner
                .lock()
                .map_err(|_| "Mutex lock error".to_string())?;
            let mut data = vec![0u8; length];
            file.read_exact_at(&mut data, offset)
                .map_err(|e| e.to_string())?;
            Ok(Binary::from(data))
        }
        #[cfg(not(unix))]
        {
            let _ = (length, offset);
            Err("read_exact_at is only available on Unix systems".to_string())
        }
    }

    #[php(name = "write")]
    pub fn write(&self, data: BinarySlice<u8>) -> Result<usize, String> {
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.write(&data).map_err(|e| format!("Write error: {}", e))
    }

    #[php(name = "write_at")]
    pub fn write_at(&self, data: BinarySlice<u8>, offset: u64) -> Result<usize, String> {
        #[cfg(unix)]
        {
            let file = self
                .inner
                .lock()
                .map_err(|_| "Mutex lock error".to_string())?;
            let bytes_written = file
                .write_at(data.as_ref(), offset)
                .map_err(|e| e.to_string())?;
            Ok(bytes_written)
        }
        #[cfg(not(unix))]
        {
            let _ = (data, offset);
            Err("write_at is only available on Unix systems".to_string())
        }
    }

    #[php(name = "write_all_at")]
    pub fn write_all_at(&self, data: BinarySlice<u8>, offset: u64) -> Result<(), String> {
        #[cfg(unix)]
        {
            let file = self
                .inner
                .lock()
                .map_err(|_| "Mutex lock error".to_string())?;
            file.write_all_at(data.as_ref(), offset)
                .map_err(|e| e.to_string())?;
            Ok(())
        }
        #[cfg(not(unix))]
        {
            let _ = (data, offset);
            Err("write_all_at is only available on Unix systems".to_string())
        }
    }

    #[php(name = "flush")]
    pub fn flush(&self) -> Result<(), String> {
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.flush().map_err(|e| format!("Flush error: {}", e))?;
        Ok(())
    }

    #[php(name = "rewind")]
    pub fn rewind(&self) -> Result<(), String> {
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.rewind().map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "stream_position")]
    pub fn stream_position(&self) -> Result<u64, String> {
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let pos = file.stream_position().map_err(|e| e.to_string())?;
        Ok(pos)
    }

    #[php(name = "seek_relative")]
    pub fn seek_relative(&self, offset: i64) -> Result<(), String> {
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.seek_relative(offset).map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "seek")]
    pub fn seek(&self, offset: i64, whence: i32) -> Result<u64, String> {
        let seek_from = match whence {
            0 => {
                if offset < 0 {
                    return Err("seek: offset must be non-negative for SEEK_SET".to_string());
                }
                SeekFrom::Start(offset as u64)
            }
            1 => SeekFrom::Current(offset),
            2 => SeekFrom::End(offset),
            _ => {
                return Err(format!(
                    "Invalid whence: {}. Use SEEK_SET(0), SEEK_CUR(1), or SEEK_END(2)",
                    whence
                ));
            }
        };
        let mut file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let pos = file.seek(seek_from).map_err(|e| e.to_string())?;
        Ok(pos)
    }

    #[php(name = "stream_len")]
    pub fn stream_len(&self) -> Result<u64, String> {
        let file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let len = file.metadata().map_err(|e| e.to_string())?.len();
        Ok(len)
    }

    #[php(name = "try_clone")]
    pub fn try_clone(&self) -> Result<Self, String> {
        let file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let clone = file.try_clone().map_err(|e| e.to_string())?;
        Ok(Self {
            inner: Mutex::new(clone),
        })
    }

    #[php(name = "set_times")]
    pub fn set_times(
        &self,
        atime: Option<&crate::systemtime::StephpCapStdSystemTime>,
        mtime: Option<&crate::systemtime::StephpCapStdSystemTime>,
    ) -> Result<(), String> {
        let file = self
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        file.set_times(
            atime.map(|t| SystemTimeSpec::Absolute(t.inner.into_std())),
            mtime.map(|t| SystemTimeSpec::Absolute(t.inner.into_std())),
        )
        .map_err(|e| e.to_string())?;
        Ok(())
    }
}
