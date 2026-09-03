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

## Phase 1 — Complétion de l'API cap-std (P1)

Deux découvertes importantes issues de la vérification de l'API cap-std 4.0.2 :

- **`Dir::set_times(path, ...)` n'existe pas dans cap-std** — c'est pourquoi ça a « ECHOUE ». cap-std n'offre le SetTimes (via fs-set-times) que sur un **handle ouvert** (`File` déjà fait ; `Dir` l'a aussi via impl blanket). Solution propre : méthode de commodité `Dir::set_times(string $path, ?StphpCapStdSystemTime $atime, ?$mtime)` qui ouvre le handle en interne (`open` pour un fichier, `open_dir` pour un répertoire) puis applique SetTimes, + éventuellement `set_own_times()` sur le handle lui-même.
- **`open_parent()` s'appelle `open_parent_dir(auth)`** et prend l'AmbientAuthority : il **sort du sandbox par conception**. À binder avec un avertissement de sécurité explicite dans les stubs/README.

Ordre de valeur :

1. `Dir::set_times` (le « A REFAIRE »), `Dir::try_exists`
2. Classe `StephpCapStdDirBuilder` (mode, recursive) + `Dir::create_dir_with`
3. `Dir::read_link_contents`, `Dir::symlink_contents`
4. `File::read_at` / `write_at` / `read_exact_at` / `write_all_at` (Unix `FileExt` — I/O à offset fixe)
5. `OpenOptions::custom_flags` (O_NOATIME, etc.), constantes `SEEK_SET/CUR/END` exportées (ext-php-rs `const`) au lieu d'entiers magiques
6. **Non-objectifs assumés** (à documenter) : fonctions ambient de `File` (affaiblissent le sandbox), sockets Unix (« not yet implemented » dans cap-std lui-même), `from_std/into_std` (sans sens en PHP), `remove_open_dir` (consomme `self` — binding ext-php-rs hasardeux, à trancher).

## Phase 2 — Durabilité des tests (P1)

1. **Auto-découverte des tests** dans `test.php` (glob sur `dir/*.php`, `file/*.php` + convention nom de fichier = nom de fonction) — supprime la double saisie include+appel, source d'oubli.
2. Argument `--filter=dir/write` pour lancer un seul test (impossible aujourd'hui : un fichier seul ne fait que définir la fonction).
3. Trous de couverture : `Metadata` unix (`rdev`, `*_nsec`), `file_type()`, `Entries` en `foreach`+`count()`, round-trip `from_unix_timestamp`, messages d'erreur (chemin inexistant), `seek` avec `whence` invalide.
4. Recommandation : **garder le harness maison** plutôt que migrer à PHPUnit (le plan cursor le disait « éventuel »). Argument : la suite doit tourner avec `-d extension=...`, PHPUnit n'apporte rien ici et ajoute une dépendance.

## Phase 3 — CI et outillage (P2)

1. **GitHub Actions** (ubuntu) : `cargo fmt --check`, `cargo clippy -D warnings`, `cargo build --release`, puis exécution de la suite PHP. Aujourd'hui Dependabot ouvre des PR sans aucune validation automatique.
2. Makefile ou justfile : `build`, `test`, `lint`, `fmt`.
3. Synchronisation de version Cargo.toml ↔ README (checklist ou script sed).

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
