# Migrations

The plugin stores its installed database version in `cten_db_version`.

On load, the migration runner compares `cten_db_version` with `CTEN_VERSION`. If a migration is needed, the plugin creates a backup in `cten_settings_migration_backup`, runs idempotent migration steps, sanitizes the resulting settings, and updates the installed database version.

Implemented migration steps:

* `1.2.0` to `1.3.0`: normalizes quick actions, initial messages, and metadata fields.
* `1.3.0` to `1.4.0`: creates the profile structure, onboarding state, pre-chat form settings, and lead qualification settings.

Migration failures are stored as a short admin-safe transient and do not intentionally fatal the plugin.
