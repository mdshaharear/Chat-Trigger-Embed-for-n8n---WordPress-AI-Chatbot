# Native n8n Setup

Native n8n mode is the server-side bridge between the browser chat UI and a stored production webhook.

Rules:

* Validate the stored URL.
* Reject browser-supplied webhook URLs.
* Keep SSRF protections in place.
* Preserve the legacy `@n8n/chat` embed as a separate compatibility mode.

