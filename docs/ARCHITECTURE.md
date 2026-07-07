# Architecture

Release note: the plugin ships bundled n8n runtime files and notices under their original licenses.

## WordPress bootstrap

The main plugin file defines constants, loads the namespaced PHP classes, checks requirements, registers activation and deactivation hooks, and loads translations.

## Settings system

All settings are stored in a single `cten_settings` option array. A shared sanitizer processes connection, appearance, message, quick action, visibility, and metadata settings.
The `render_mode` setting controls whether the chat renders in the global footer, inside an Elementor widget, or in both places.

## Asset build

Frontend runtime assets are loaded from `dist/`. The official n8n chat runtime and stylesheet are vendored locally in `dist/vendor/n8n-chat/`.

## Official @n8n/chat integration

The frontend uses the official package bundle and the `createChat()` API. The connection layer passes the production webhook URL, request method, headers, keys, language, initial messages, metadata, and streaming flag.

## Compatibility adapter

Quick actions are implemented through a single DOM adapter that writes into the embedded composer and presses the send button. This keeps the brittle selector logic isolated.

## Elementor integration

The plugin exposes an Elementor widget that renders the same chat shell and uses the same public config as the global footer mode.

## Responsive design

Desktop, tablet, and mobile presentation are controlled through official CSS variables and plugin wrapper styles. Mobile uses viewport-safe sizing and reduced-motion fallbacks.

## Hooks and filters

Documented filters and actions include:

* `cten_chat_should_display`
* `cten_public_chat_config`
* `cten_public_metadata`
* `cten_public_chat_config_render`
* `cten_before_chat_render`
* `cten_after_chat_render`
* `cten_appearance_css_vars`
* `cten_public_chat_config`

## Phase 2 and Phase 3 extension points

Future phases can add licensing, subscription management, cloud account features, analytics, and more advanced automation without changing the single-settings-array architecture.
