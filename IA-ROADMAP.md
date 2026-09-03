# IA-ROADMAP — Corrections et évolutions de stephp-cap-std

> Roadmap établie le 2026-09-03 après analyse complète du code, de la suite de tests (verte au moment de l'analyse), de l'API réelle de cap-std 4.0.2 (vérifiée sur docs.rs) et des notes de travail (`NOTES-TMP.txt`, `.cursor/plans/`). Chaque phase peut être livrée de façon incrémentale.

> **Décisions prises avant implémentation** : IA-ROADMAP.md reste en français ; un commit par phase ; version 0.6.0 en fin de parcours ; périmètre = phases 0→3 ; `open_parent_dir` ne sera PAS exposé (omis volontairement, casse la promesse anti-traversal — documenté comme non-objectif).

## Phase 0 — Corrections immédiates (P0, ~1 session) — ✅ TERMINÉE

| # | Correction | Détail |
|---|---|---|
| 0.1 | **README : exemple cassé** | `new StephpCapStdOpenOptions()` lève `"You cannot instantiate this class from PHP"` (vérifié en live). Remplacer par `StephpCapStdOpenOptions::new()`. Corriger aussi `cd php-cap-std` → `stephp-cap-std` (ligne 25). |
| 0.2 | **README : API incomplète** | Manquent : `Dir::try_clone`, `File::try_clone`, `File::set_times`, `Metadata::file_type()`, `SystemTime::from_unix_timestamp`, `OpenOptions::mode()`, `rdev`/`size`/`*_nsec`, `File::seek_relative`. |
| 0.3 | **Stubs** | DocBlocks (plan cursor §5), retirer le `__construct()` public factice et `StphpCapStdEntries::new(array)` (constructeur interne, ne doit pas être exposé), préciser que `read()` renvoie des octets bruts. |
| 0.4 | **Cohérence `cfg(unix)`** | `StephpCapStdPermissions::new()` est gated sur toute la méthode, contrairement à la convention posée au commit 8cc6228. `OpenOptions::mode()` no-op silencieux sur non-unix → devrait retourner `Result<(), String>`. |
| 0.5 | **Nettoyage** | Migrer `NOTES-TMP.txt` vers des tickets/ROADMAP puis le supprimer. |

## Phase 1 — Complétion de l'API cap-std (P1) — ✅ TERMINÉE

Deux découvertes importantes issues de la vérification de l'API cap-std 4.0.2 :

- **`Dir::set_times(path, ...)` n'existe pas dans cap-std** — cap-std n'offre le SetTimes que sur un handle ouvert. Implémenté de manière 100% sûre (`forbid(unsafe_code)`) via `rustix::fs::utimensat` (POSIX direct et sûr sur `dirfd`) pour `set_own_times()` et `set_times(path, atime, mtime)`, sans aucun bloc `unsafe` ni manipulation FFI.
- **`open_parent_dir(auth)`** : omis volontairement car il sort du sandbox par conception.

Ordre de valeur :

1. `Dir::set_times`, `Dir::set_own_times`, `Dir::try_exists` (implémentés et testés en safe Rust)
2. Classe `StephpCapStdDirBuilder` (mode, recursive) + `Dir::create_dir_with` (implémentés et testés)
3. `Dir::read_link_contents`, `Dir::symlink_contents` (implémentés et testés)
4. `File::read_at` / `write_at` / `read_exact_at` / `write_all_at` (Unix `FileExt` — I/O à offset fixe, implémentés et testés)
5. `OpenOptions::custom_flags`, constantes `StephpCapStdFile::SEEK_SET/CUR/END` (implémentés et testés)
6. **Non-objectifs assumés** (documentés) : fonctions ambient de `File` (affaiblissent le sandbox), sockets Unix (« not yet implemented » dans cap-std lui-même), `from_std/into_std` (sans sens en PHP), `open_parent_dir` (casse l'isolation), `remove_open_dir` (consomme `self`).

## Phase 2 — Durabilité des tests (P1) — ✅ TERMINÉE

1. **Auto-découverte des tests** dans `test.php` (scan récursif des fichiers `.php` dans `php/` et exécution automatique des fonctions déclarées dans le namespace `tests\`).
2. Argument `--filter=<pattern>` pour lancer un sous-ensemble de tests (ex: `--filter=dir/write`, `--filter=offset_io`).
3. Comblement des trous de couverture : `Metadata` unix (`dev`, `ino`, `mode`, `nlink`, `uid`, `gid`, `rdev`, `size`, `*_nsec`, `blksize`, `blocks`), `file_type()`, `Entries` (`count()`, `foreach`, méthodes d'itération), round-trip `from_unix_timestamp`, messages d'erreur et cas d'erreurs `seek`.
4. Maintien du harness maison léger et rapide.

## Phase 3 — CI et outillage (P2) — ✅ TERMINÉE

1. **GitHub Actions** (`.github/workflows/ci.yml`) : validation automatique sous Ubuntu (`cargo fmt --check`, `cargo clippy -D warnings`, `cargo build --release`, suite de tests PHP).
2. **Makefile** : cibles `build`, `build-debug`, `test`, `lint`, `fmt`, `fmt-check`, `check`, `clean`.
3. Synchronisation de la version à **0.6.0** dans `Cargo.toml` et `README.md`, avec interdiction stricte de code unsafe (`[lints.rust] unsafe_code = "forbid"`).

## Phase 4 — Évolutions stratégiques (P3)

1. **Décision Windows** : soit support réel (CI + tests `warning()`-skippés, pattern déjà présent dans `permissions_extended.php`), soit Unix-only assumé (suppression des `cfg_attr(abi_vectorcall)` et des fallbacks).
2. **Distribution** : les `.so` ext-php-rs sont liés à la version PHP et au mode ZTS/NTS — un release précompilé exigerait une matrice de builds. Minimum viable : documenter la compilation from-source dans le README + releases GitHub taguées.
3. Section benchmarks (overhead FFI) dans le README.

## Séquençage conseillé

**0 → 1.1/1.2 → 2.1 → 3.1** (la CI sécurise la suite), puis le reste.

---

## Suivi d'exécution

| Date | Phase | Statut | Détail |
|---|---|---|---|
| 2026-09-03 | Phase 0 | ✅ Terminée | README (exemple `::new()`, nom du dépôt, API complétée) ; stubs réécrits avec DocBlocks, `__construct` factices et `Entries::new()` supprimés ; `Permissions::new()` et `OpenOptions::mode()` retournent désormais `Result` (breaking, justifie 0.6.0) ; constructeur interne d'Entries sorti du `#[php_impl]` (masqué côté PHP) ; `NOTES-TMP.txt` supprimé (idées restantes reprises dans Phase 4 / non-objectifs). Suite PHP : 114 OK / 0 KO. |
| 2026-09-04 | Phase 1 | ✅ Terminée | Ajout de `Dir::try_exists`, `Dir::set_own_times`, `Dir::set_times` (100% safe via `rustix::fs::utimensat`), `StephpCapStdDirBuilder` + `Dir::create_dir_with`, `Dir::read_link_contents`, `Dir::symlink_contents`, `File::read_at`/`read_exact_at`/`write_at`/`write_all_at`, `OpenOptions::custom_flags`, constantes `StephpCapStdFile::SEEK_*`. Mise à jour stubs et README. |
| 2026-09-04 | Phase 2 | ✅ Terminée | Refonte du runner `php/test.php` avec auto-découverte et support du filtrage `--filter=...`. Nouveaux tests pour `metadata_extended`, `file_type`, `entries_iteration`, `systemtime_roundtrip`, `error_messages`, `seek_invalid`. Suite PHP : 167 OK / 0 KO. |
| 2026-09-04 | Phase 3 | ✅ Terminée | Création du workflow GitHub Actions CI (`.github/workflows/ci.yml`), création du `Makefile` complet, bump de version à **0.6.0** dans `Cargo.toml` et `README.md`, interdiction stricte de tout code `unsafe` (`[lints.rust] unsafe_code = "forbid"` et `#![forbid(unsafe_code)]`). |
