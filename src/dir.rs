#![cfg_attr(windows, feature(abi_vectorcall))]

use crate::dirbuilder::StephpCapStdDirBuilder;
use crate::entries;
use crate::file;
use crate::metadata;
use crate::metadata::StephpCapStdMetadata;
use crate::openoptions::StephpCapStdOpenOptions;
use crate::permissions::StephpCapStdPermissions;
use crate::systemtime::StephpCapStdSystemTime;
use ext_php_rs::binary::Binary;
use ext_php_rs::binary_slice::BinarySlice;
use ext_php_rs::prelude::*;
use std::sync::Mutex;

#[php_class]
pub struct StephpCapStdDir {
    pub inner: cap_std::fs::Dir,
}

#[php_impl]
impl StephpCapStdDir {
    #[php(name = "entries")]
    pub fn entries(&self) -> Result<entries::StephpCapStdEntries, String> {
        let read_dir = self.inner.entries().map_err(|e| e.to_string())?;
        let mut entries = Vec::new();
        for entry in read_dir {
            let entry = entry.map_err(|e| e.to_string())?;
            if let Ok(name) = entry.file_name().into_string() {
                entries.push(name);
            }
        }
        Ok(entries::StephpCapStdEntries::new(entries))
    }

    #[php(name = "read_dir")]
    pub fn read_dir(&self, path: &str) -> Result<entries::StephpCapStdEntries, String> {
        let read_dir = self.inner.read_dir(path).map_err(|e| e.to_string())?;
        let mut entries = Vec::new();
        for entry in read_dir {
            let entry = entry.map_err(|e| e.to_string())?;
            if let Ok(name) = entry.file_name().into_string() {
                entries.push(name);
            }
        }
        Ok(entries::StephpCapStdEntries::new(entries))
    }

    #[php(name = "open_dir")]
    pub fn open_dir(&self, path: &str) -> Result<Self, String> {
        let dir = self.inner.open_dir(path).map_err(|e| e.to_string())?;
        Ok(StephpCapStdDir { inner: dir })
    }

    #[php(name = "open")]
    pub fn open(&self, path: &str) -> Result<file::StephpCapStdFile, String> {
        let fd = self.inner.open(path).map_err(|e| e.to_string())?;
        Ok(file::StephpCapStdFile {
            inner: Mutex::new(fd),
        })
    }

    #[php(name = "open_with")]
    pub fn open_with(
        &self,
        path: &str,
        options: &StephpCapStdOpenOptions,
    ) -> Result<file::StephpCapStdFile, String> {
        let options = options
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        let fd = self
            .inner
            .open_with(path, &options)
            .map_err(|e| e.to_string())?;
        Ok(file::StephpCapStdFile {
            inner: Mutex::new(fd),
        })
    }

    #[php(name = "create")]
    pub fn create(&self, path: &str) -> Result<file::StephpCapStdFile, String> {
        let fd = self.inner.create(path).map_err(|e| e.to_string())?;
        Ok(file::StephpCapStdFile {
            inner: Mutex::new(fd),
        })
    }

    #[php(name = "create_dir")]
    pub fn create_dir(&self, path: &str) -> Result<(), String> {
        match self.inner.create_dir(path) {
            Ok(_) => Ok(()),
            Err(e) => Err(e.to_string()),
        }
    }

    #[php(name = "create_dir_all")]
    pub fn create_dir_all(&self, path: &str) -> Result<(), String> {
        match self.inner.create_dir_all(path) {
            Ok(_) => Ok(()),
            Err(e) => Err(e.to_string()),
        }
    }

    #[php(name = "create_dir_with")]
    pub fn create_dir_with(
        &self,
        path: &str,
        builder: &StephpCapStdDirBuilder,
    ) -> Result<(), String> {
        let builder = builder
            .inner
            .lock()
            .map_err(|_| "Mutex lock error".to_string())?;
        self.inner
            .create_dir_with(path, &builder)
            .map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "copy")]
    pub fn copy(&self, from: &str, to_dir: &StephpCapStdDir, to: &str) -> Result<u64, String> {
        match self.inner.copy(from, &to_dir.inner, to) {
            Ok(size) => Ok(size),
            Err(e) => Err(e.to_string()),
        }
    }

    #[php(name = "rename")]
    pub fn rename(&self, from: &str, to_dir: &StephpCapStdDir, to: &str) -> Result<(), String> {
        match self.inner.rename(from, &to_dir.inner, to) {
            Ok(_) => Ok(()),
            Err(e) => Err(e.to_string()),
        }
    }

    #[php(name = "dir_metadata")]
    pub fn dir_metadata(&self) -> Result<metadata::StephpCapStdMetadata, String> {
        let metadata = self.inner.dir_metadata().map_err(|e| e.to_string())?;
        Ok(metadata::StephpCapStdMetadata { inner: metadata })
    }

    #[php(name = "canonicalize")]
    pub fn canonicalize(&self, path: &str) -> Result<String, String> {
        let canon = self.inner.canonicalize(path).map_err(|e| e.to_string())?;
        Ok(canon.to_string_lossy().to_string())
    }

    #[php(name = "read")]
    pub fn read(&self, path: &str) -> Result<Binary<u8>, String> {
        let data = self.inner.read(path).map_err(|e| e.to_string())?;
        Ok(Binary::from(data))
    }

    #[php(name = "read_to_string")]
    pub fn read_to_string(&self, path: &str) -> Result<String, String> {
        let s = self.inner.read_to_string(path).map_err(|e| e.to_string())?;
        Ok(s)
    }

    #[php(name = "write")]
    pub fn write(&self, path: &str, data: BinarySlice<u8>) -> Result<(), String> {
        self.inner
            .write(path, data.as_ref())
            .map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "remove_dir")]
    pub fn remove_dir(&self, path: &str) -> Result<(), String> {
        self.inner.remove_dir(path).map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "remove_dir_all")]
    pub fn remove_dir_all(&self, path: &str) -> Result<(), String> {
        self.inner.remove_dir_all(path).map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "remove_file")]
    pub fn remove_file(&self, path: &str) -> Result<(), String> {
        self.inner.remove_file(path).map_err(|e| e.to_string())?;
        Ok(())
    }

    #[php(name = "is_file")]
    pub fn is_file(&self, path: &str) -> bool {
        self.inner.is_file(path)
    }

    #[php(name = "is_dir")]
    pub fn is_dir(&self, path: &str) -> bool {
        self.inner.is_dir(path)
    }

    #[php(name = "exists")]
    pub fn exists(&self, path: &str) -> bool {
        self.inner.exists(path)
    }

    #[php(name = "try_exists")]
    pub fn try_exists(&self, path: &str) -> Result<bool, String> {
        self.inner.try_exists(path).map_err(|e| e.to_string())
    }

    #[php(name = "hard_link")]
    pub fn hard_link(&self, src: &str, dst_dir: &StephpCapStdDir, dst: &str) -> Result<(), String> {
        match self.inner.hard_link(src, &dst_dir.inner, dst) {
            Ok(_) => Ok(()),
            Err(e) => Err(e.to_string()),
        }
    }

    #[php(name = "metadata")]
    pub fn metadata(&self, path: &str) -> Result<StephpCapStdMetadata, String> {
        let metadata = self.inner.metadata(path).map_err(|e| e.to_string())?;
        Ok(metadata::StephpCapStdMetadata { inner: metadata })
    }

    #[php(name = "read_link")]
    pub fn read_link(&self, path: &str) -> Result<String, String> {
        let read = self.inner.read_link(path).map_err(|e| e.to_string())?;
        Ok(read.to_string_lossy().to_string())
    }

    #[php(name = "read_link_contents")]
    pub fn read_link_contents(&self, path: &str) -> Result<String, String> {
        let path_buf = self
            .inner
            .read_link_contents(path)
            .map_err(|e| e.to_string())?;
        Ok(path_buf.to_string_lossy().to_string())
    }

    #[php(name = "symlink_metadata")]
    pub fn symlink_metadata(&self, path: &str) -> Result<StephpCapStdMetadata, String> {
        let metadata = self
            .inner
            .symlink_metadata(path)
            .map_err(|e| e.to_string())?;
        Ok(metadata::StephpCapStdMetadata { inner: metadata })
    }

    #[php(name = "set_permissions")]
    pub fn set_permissions(
        &self,
        path: &str,
        perm: &StephpCapStdPermissions,
    ) -> Result<(), String> {
        #[cfg(unix)]
        {
            let permissions = perm
                .inner
                .lock()
                .map_err(|_| "Mutex lock error".to_string())?
                .clone();
            self.inner
                .set_permissions(path, permissions)
                .map_err(|e| e.to_string())?;
            Ok(())
        }
        #[cfg(not(unix))]
        {
            let _ = (path, perm);
            Err("set_permissions is only available on Unix systems".to_string())
        }
    }

    #[php(name = "symlink")]
    pub fn symlink(&self, original: &str, link: &str) -> Result<(), String> {
        #[cfg(unix)]
        {
            self.inner
                .symlink(original, link)
                .map_err(|e| e.to_string())?;
            Ok(())
        }
        #[cfg(not(unix))]
        {
            let _ = (original, link);
            Err("symlink is only available on Unix systems".to_string())
        }
    }

    #[php(name = "symlink_contents")]
    pub fn symlink_contents(&self, original: &str, link: &str) -> Result<(), String> {
        #[cfg(unix)]
        {
            self.inner
                .symlink_contents(original, link)
                .map_err(|e| e.to_string())?;
            Ok(())
        }
        #[cfg(not(unix))]
        {
            let _ = (original, link);
            Err("symlink_contents is only available on Unix systems".to_string())
        }
    }

    #[php(name = "set_own_times")]
    pub fn set_own_times(
        &self,
        atime: Option<&StephpCapStdSystemTime>,
        mtime: Option<&StephpCapStdSystemTime>,
    ) -> Result<(), String> {
        #[cfg(unix)]
        {
            use std::os::unix::io::AsRawFd;
            let empty_path = std::ffi::CString::new("").map_err(|e| e.to_string())?;
            let times = times_to_timespec(atime, mtime);
            let res = unsafe {
                libc::utimensat(
                    self.inner.as_raw_fd(),
                    empty_path.as_ptr(),
                    times.as_ptr(),
                    libc::AT_EMPTY_PATH,
                )
            };
            if res != 0 {
                return Err(std::io::Error::last_os_error().to_string());
            }
            Ok(())
        }
        #[cfg(not(unix))]
        {
            let _ = (atime, mtime);
            Err("set_own_times is only available on Unix systems".to_string())
        }
    }

    #[php(name = "set_times")]
    pub fn set_times(
        &self,
        path: &str,
        atime: Option<&StephpCapStdSystemTime>,
        mtime: Option<&StephpCapStdSystemTime>,
    ) -> Result<(), String> {
        #[cfg(unix)]
        {
            use std::os::unix::io::AsRawFd;
            if path == "." || path.is_empty() {
                return self.set_own_times(atime, mtime);
            }
            let c_path = std::ffi::CString::new(path).map_err(|e| e.to_string())?;
            let times = times_to_timespec(atime, mtime);
            let res = unsafe {
                libc::utimensat(self.inner.as_raw_fd(), c_path.as_ptr(), times.as_ptr(), 0)
            };
            if res != 0 {
                return Err(std::io::Error::last_os_error().to_string());
            }
            Ok(())
        }
        #[cfg(not(unix))]
        {
            let _ = (path, atime, mtime);
            Err("set_times is only available on Unix systems".to_string())
        }
    }

    #[php(name = "try_clone")]
    pub fn try_clone(&self) -> Result<Self, String> {
        let clone = self.inner.try_clone().map_err(|e| e.to_string())?;
        Ok(Self { inner: clone })
    }
}

#[cfg(unix)]
fn times_to_timespec(
    atime: Option<&StephpCapStdSystemTime>,
    mtime: Option<&StephpCapStdSystemTime>,
) -> [libc::timespec; 2] {
    let to_spec = |opt: Option<&StephpCapStdSystemTime>| match opt {
        Some(t) => {
            let dur = t
                .inner
                .duration_since(cap_std::time::SystemTime::from_std(std::time::UNIX_EPOCH))
                .unwrap_or_default();
            libc::timespec {
                tv_sec: dur.as_secs() as libc::time_t,
                tv_nsec: dur.subsec_nanos() as libc::c_long,
            }
        }
        None => libc::timespec {
            tv_sec: 0,
            tv_nsec: libc::UTIME_OMIT,
        },
    };
    [to_spec(atime), to_spec(mtime)]
}
