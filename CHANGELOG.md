# Changelog

## 2.0.0

* Promoted the plugin to a stable release line with production-focused metadata.
* Added Elementor widget rendering with global footer, widget-only, and both-mode display control.
* Hardened frontend asset registration and removed obsolete internal release ZIP archives.

## 2.0.0-alpha.3

* Finalized the native provider contract with `validate_configuration()`, `test_connection()`, and `normalize_response()`.
* Tightened n8n URL validation and added the release verification script and toolchain docs.
* Bumped release metadata to `2.0.0-alpha.3`.

## 2.0.0-alpha.2

* Added the native AI core bridge for provider connections, chatbot records, a public REST chat endpoint, and the browser-native chat UI.
* Preserved the legacy n8n embed mode while allowing the plugin to switch to native chatbots only when one is configured and visible.
* Updated release metadata to `2.0.0-alpha.2`.

## 2.0.0-alpha.1

* Renamed the plugin display surface to `AI Chat Builder for WordPress - OpenAI, Gemini & n8n` while preserving the existing slug, text domain, and legacy n8n compatibility path.
* Added the first version of the Phase 8 migration plan and native AI architecture scaffolding.
* Updated version metadata to `2.0.0-alpha.1`.

## 1.5.0

Runtime Lab, self-test, and deployment hardening release.

### Added

* Administrator-only Runtime Lab page with environment, plugin file, database, configuration, profile resolution, mock chat, live n8n, browser test, analytics, scheduled task, security, and export sections.
* Structured self-test results with normalized status, severity, technical detail, and suggested fixes.
* Safe-mode controls with automatic failure tracking and recovery options.
* Site Health integration with plugin-specific tests and debug information.
* Temporary runtime test token generation and safe event beacon support.
* Mock n8n contract server and deployment/playground documentation.

### Changed

* Bumped plugin version to `1.5.0`.
* Connected profile resolution to simulator context and runtime-lab reports.
* Disabled unsafe or misleading admin controls where production behavior was not safely implemented.
* Hardened public runtime behavior for safe mode and temporary event reporting.

### Fixed

* Prevented runtime-lab actions from accepting arbitrary live-test URLs.
* Added cron scheduling and cleanup hooks for analytics and runtime-lab test data.
* Reduced the risk of public runtime loading on safe-mode-protected sites.

## 1.4.1

Runtime stabilization and dead-setting audit release.

### Changed

* Bumped plugin version to `1.4.1` because confirmed runtime wiring fixes were required.
* Split the public controller from the official `@n8n/chat` runtime chunk so lazy loading, hover preload, and delayed loading have real runtime behavior.
* Disabled unsupported conversation menu, export, sound, and unread badge controls instead of saving fake values.
* Restricted pre-chat submission mode to metadata until first-message injection can be supported safely by the official runtime.

### Fixed

* Connected `session_expiry_days` to browser session storage expiry.
* Connected `show_online_indicator`, `lazy_load_runtime`, `preload_on_hover`, `load_after_delay_seconds`, launcher delay, and auto-open behavior to frontend runtime code.
* Connected stored profile page rules and campaign rules to profile resolution.
* Added diagnostics output for competing profile resolution decisions.
* Hardened release CSS validation to require quick actions, dynamic options, pre-chat, contact fallback, and lazy launcher selectors.

## 1.4.0

Advanced professional product enhancement release.

### Added

* Versioned migration system with installed database version tracking, settings backup, idempotent 1.3.0 and 1.4.0 migration steps, and safe failure notices.
* Multi-profile chatbot foundation with up to 20 profiles, profile priorities, one default profile, profile resolution filters, example profiles, and profile admin search.
* Setup Wizard page with saved progress, production URL guidance, origin copy action, and explicit enablement reminder.
* Pre-chat form foundation with optional session-level completion, safe field validation, no local submission storage, and metadata handoff to n8n.
* Lead qualification settings and safe `[[LEAD_STATUS:hot|warm|cold]]` marker parsing.
* Expanded preset list: Elegant Gold, Modern Green, Corporate Blue, and High Contrast.
* Release asset size regression test for generated JS and CSS.

### Changed

* Bumped plugin version to `1.4.0`.
* Quick action sanitizer now supports up to 30 actions.
* Initial message sanitizer now supports up to 20 messages.
* Public runtime config now includes resolved profile, pre-chat, and lead qualification context.
* Production CSS now includes pre-chat form styles and stronger mobile placement.

### Fixed

* Fixed unchecked admin checkboxes retaining stale truthy values by emitting a hidden fallback value.
* Fixed lead status markers leaking into visitor-visible assistant text.

## 1.3.0

Professional enhancement and runtime hardening release.

### Added

* Launcher, Behaviour, Analytics, and Diagnostics admin sections
* Repeatable initial messages and up to 12 validated quick actions
* Opt-in local analytics table with indexed event summaries and retention settings
* Contact fallback settings for WhatsApp, email, and contact-page links
* Metadata field controls and safe page URL handling without unknown query strings
* Diagnostics report for plugin, WordPress, PHP, asset, webhook, and analytics status

### Changed

* Bumped plugin version to `1.3.0`
* Regenerated frontend assets with stable production filenames
* Improved public runtime guards for rapid sends, input length, and new-conversation text
* Expanded appearance color controls and theme preset choices
* Improved admin UX with unsaved-change warning and diagnostics copying

### Fixed

* Prevented dynamic option double submission during rapid clicks
* Avoided always sending browser metadata when related fields are disabled
* Removed reliance on global browser `Event` constructors in adapter interactions
* Ensured analytics data is removed on uninstall only when data deletion is enabled

## 1.2.0

Internal-use finalization release.

### Added

* Dashboard setup completion and webhook diagnostics
* Connection page URL validation diagnostics
* Expanded admin preview states and live updates
* Quick-start, production checklist, rollback, and system-message docs

### Changed

* Bumped the plugin to `1.2.0`
* Updated release docs and licensing notices
* Cleaned remaining encoding artifacts in admin strings

## 1.1.0

Validation, packaging, and licensing separation release.

### Added

* TypeScript validation for the public chat runtime
* Session helpers for stable browser session IDs
* Quick-action normalization and dynamic option parsing
* Compatibility adapter for the embedded composer
* `THIRD_PARTY_NOTICES.md` for bundled n8n assets

### Changed

* Bumped plugin version to `1.1.0`
* Updated documentation to call out bundled third-party licenses
* Regenerated the production frontend bundle

### Tested

* `npm run build`
* `npm run lint`
* `npm run typecheck`
* `npm test`
* `php -l` on all PHP files
