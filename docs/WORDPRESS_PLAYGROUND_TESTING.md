# WordPress Playground Testing

Use `tools/playground/blueprint.json` to boot a disposable WordPress environment and load the plugin ZIP into the admin dashboard.

## What It Does

* Installs a supported WordPress version.
* Uploads the packaged plugin ZIP from the local workspace.
* Activates the plugin when the workflow supports it.
* Opens the Runtime Lab page for manual checks.

## What It Does Not Do

* It does not prove a real n8n workflow works.
* It does not verify Redis-backed session memory.
* It does not replace a real browser test on the owner's staging site.

## Recommended Flow

1. Start WordPress Playground with the blueprint.
2. Confirm the plugin appears in the Plugins screen.
3. Open Runtime Lab.
4. Run safe tests and mock chat tests.
5. If you have a real n8n endpoint, run the live test only after reading the confirmation message.
