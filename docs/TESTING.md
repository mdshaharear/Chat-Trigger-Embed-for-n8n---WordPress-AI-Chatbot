# Testing Checklist

Open-source release note: validate the bundled n8n assets together with their original notices.

## PHP

* `php -l` on every PHP file
* Activate the plugin on a site running PHP 8.1+
* Confirm no fatal errors when the plugin is deactivated or uninstalled
* Confirm admin notices appear when requirements are not met

## Frontend

* Confirm the launcher renders only when enabled and configured
* Confirm the widget does not appear in wp-admin
* Confirm quick actions hide after the first interaction
* Confirm the chat stays keyboard accessible
* Confirm escape closes the embedded widget
* Confirm mobile layout does not cause horizontal scrolling

## n8n integration

* Verify the production webhook URL is saved
* Verify Allowed Origins includes the WordPress origin
* Verify the workflow is active
* Verify Embedded Chat mode is selected
* Verify previous-session loading works when memory is available

## Security

* Verify nonces on settings and tools pages
* Verify capability checks on every admin action
* Verify no private WordPress data is sent as metadata
* Verify no CDN URLs are present in production assets

## Packaging

* Verify `node_modules` is excluded from the ZIP
* Verify `dist/` is excluded from the ZIP archive member listing if you package from the project root
* Verify the ZIP does not include itself
