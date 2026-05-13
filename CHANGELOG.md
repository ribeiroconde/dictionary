# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Released]

## [1.0.0] - 2026-04-05

### Added

- **Dictionary wizard** — slide-over wizard with three steps (Database, Eloquent, Review) for defining table schema and generation options.
- **Code preview** — live code preview in the Review step for migration, model, factory, seeder, and Filament resource before generation.
- **Blueprint persistence** — all blueprints are stored in `dictionary_blueprints` and can be re-opened and edited at any time.
- **Revision history** — every generation is recorded as a versioned snapshot in `dictionary_blueprint_revisions`; older revisions can be previewed and loaded back into the editor.
- **Three generation modes** — `create` (only new files), `merge` (update Dictionary-managed sections, preserve customisations), `replace` (overwrite all generated artifacts).
- **Schema safety** — blocking detection for unsafe required-column additions on non-empty tables; deferred execution for destructive schema changes (column drops, soft-delete removal, likely renames) until explicitly enabled.
- **Soft deletes support** — generates `SoftDeletes` trait, `TrashedFilter`, bulk restore/force-delete actions, and `getEloquentQuery()` override automatically.
- **Foreign key relationships** — auto-detects `_id` / `_uuid` / `_ulid` suffixes and generates `Select` relationship components with configurable title columns.
- **Primary key types** — supports `auto-increment`, `UUID`, and `ULID` primary keys.
- **Filament v3 / v4 / v5 dual structure** — generates a flat monolithic resource for Filament v3 and a domain-split structure (Resource + Form/Infolist schemas + Table) for v4/v5. Switch via `DICTIONARY_FILAMENT_VERSION`.
- **Smart legacy v3 cleanup** — automatically detects and deletes unmodified legacy v3 resource files when running in v4/v5 mode; flags customised files with a persistent warning instead of deleting them.
- **AST-based structural comparison** — uses `nikic/php-parser` to compare the skeleton of legacy v3 resource files (ignoring generated method bodies) so that blueprint column changes do not incorrectly classify a file as customised.
- **Factory generation** — produces a typed `HasFactory` factory with sensible Faker defaults per column type.
- **Seeder generation** — produces a seeder wired to the factory.
- **`dictionary:install` command** — publishes config, runs migrations, and prints a getting-started checklist.
- **`dictionary:upgrade` command** — backfills initial revisions for existing blueprints after upgrading from pre-1.0.0 releases; supports `--dry-run`.
- **Extension registries** — `DictionaryBlockRegistry`, `DictionaryCapabilityRegistry`, `DictionaryUiExtensionRegistry`, and `BlueprintGenerationHookRegistry` for extending Dictionary from third-party packages.
- **`DictionaryPlugin` Filament plugin** — configurable render hook, icon, button colour, and capability resolver.
- **Merge-aware resource updater** — `FilamentResourceUpdater` uses `nikic/php-parser` to surgically merge newly generated form/table/infolist sections into existing resource files without touching user customisations.
- **`GeneratedCodeFormatter`** — optionally runs Laravel Pint on every generated file after writing; controlled via `dictionary.format_generated_files` config.
- **`RegenerationPlanner`** — computes the minimal set of schema operations needed to sync the database to the current blueprint before any files are written.

### Fixed

- Livewire morphdom no longer overwrites Alpine.js-managed visibility state on the wizard footer during reactive re-renders.
- Legacy v3 resource files with methods in a different order than the stub are now correctly classified as unmodified (parser-based structural comparison with sorted member parts).
- Legacy v3 cleanup now runs unconditionally after generation, regardless of whether the "Generate Filament Resource" option is checked.
