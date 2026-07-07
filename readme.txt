=== AI Chat Builder for WordPress - OpenAI, Gemini & n8n ===
Contributors: mdshaharear
Author: MD Shaharear
Author URI: https://shaharear.com.bd
Tags: n8n Chat Trigger, n8n chat embed, WordPress n8n chatbot, WordPress AI chatbot, embedded AI chat, n8n workflow chatbot, AI customer support, lead generation chatbot
Requires at least: 6.5
Requires PHP: 8.1
Tested up to: 6.5
Stable tag: 2.0.0
License: GPL-2.0-or-later

Production release 2.0.0. Bundled third-party components retain their original licenses; see `THIRD_PARTY_NOTICES.md`.

Build native AI chatbots for WordPress with OpenAI, Gemini, n8n, and hybrid workflows while preserving the legacy n8n embed mode for backward compatibility.
Elementor users can place the chatbot as a widget on selected pages or keep the global footer launcher.
The plugin includes a guided setup wizard, starter defaults, admin day/night theme mode, and GitHub release-based update checks for open-source publishing.

== Description ==
AI Chat Builder for WordPress is an independent third-party WordPress plugin for building native AI chatbots and preserving the legacy n8n embed path. It helps you manage chatbot labels, appearance, and compatibility settings without sending telemetry or using a remote SaaS account.

This plugin is not affiliated with or endorsed by n8n.

The plugin code is GPL-2.0-or-later. Bundled n8n assets and notices remain under their original licenses.

Features:

* Native AI builder shell for future OpenAI, Gemini, and hybrid chatbots
* Legacy n8n Chat Trigger production webhook connection
* n8n chat embed using the official chat runtime for backward compatibility
* WordPress AI chatbot launcher and window controls
* Elementor widget placement for page-builder layouts
* AI customer support and lead generation chatbot use cases
* Embedded AI chat with session memory support
* Page targeting and device visibility rules
* Export and import of plugin settings
* Guided onboarding, starter defaults, and admin light/dark/system theme mode
* GitHub release update checking for easier open-source maintenance
* Production packaging with explicit third-party notices

== Installation ==
1. Upload the plugin ZIP in WordPress.
2. Activate the plugin.
3. Open the plugin dashboard and follow the setup wizard.
4. Configure the n8n production Chat Trigger URL.
5. Add your WordPress origin to Allowed Origins in n8n.
6. Choose `Global Footer`, `Elementor Widget`, or `Both` for display mode.
7. If you use Elementor, drag the `AI Chat Builder` widget into your layout.

== Updating ==
This release can check GitHub releases for newer versions. Publish a new GitHub Release, then refresh the update cache from the dashboard or WordPress Updates screen.

== Frequently Asked Questions ==
= Is this an official n8n product? =
No. It is an independent third-party integration.

= Can I hide the public webhook URL? =
No. The browser must call the public Chat Trigger URL directly.
