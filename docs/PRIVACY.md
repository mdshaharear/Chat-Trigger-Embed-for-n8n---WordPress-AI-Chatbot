# Privacy Notes

Chat Trigger Embed for n8n sends chat traffic to the n8n Chat Trigger URL configured by the site administrator. The plugin itself does not provide a remote SaaS account and does not send telemetry to the plugin author.

## What The Plugin Stores

The plugin stores its WordPress option values in `cten_settings`. These settings include appearance choices, visibility rules, message labels, quick actions, connection settings, metadata toggles, fallback links, and analytics preferences.

Local analytics are disabled by default. When enabled, the plugin creates a local WordPress database table for event counters such as widget loaded, chat opened, quick action clicked, dynamic option clicked, user message sent, assistant response received, and errors. The analytics design avoids full visitor messages, raw IP addresses, email addresses, cookies, authorization headers, and visitor fingerprinting.

## What n8n May Receive

The configured n8n workflow receives chat messages and any metadata fields explicitly enabled by the site administrator. Metadata controls include page title, safe page URL, page path, referrer, browser language, browser timezone, UTM fields, industry, post ID, post type, plugin version, and optional theme name.

If the optional pre-chat form is enabled, its allowed fields are sent to the configured n8n workflow as metadata or as the first message, depending on administrator settings. The plugin does not store pre-chat submissions locally by default.

The plugin does not send WordPress cookies, passwords, admin nonces, authentication tokens, private profile data, or unknown full query strings.

## Site Administrator Responsibility

Site administrators are responsible for describing their own n8n workflow, data handling, retention, and third-party processors in their site privacy policy. This plugin does not automatically edit the WordPress privacy policy.

Suggested privacy-policy text:

```text
This site uses Chat Trigger Embed for n8n to provide an AI chat experience. Chat messages are sent to our configured n8n workflow for processing. Depending on our settings, limited page context such as the page title, page path, browser language, timezone, and campaign parameters may also be sent. Local analytics, if enabled, store event counts only and do not store full chat messages, raw IP addresses, emails, or cookies. You can contact us to request information about chat data handled by our n8n workflow.
```

## Clearing Data

Administrators can reset plugin settings from Tools. If "Delete Plugin Data on Uninstall" is enabled, uninstall removes the plugin settings and the local analytics table.
