# OpenAI Setup

OpenAI support in Phase 8 is designed for server-side use only.

Requirements:

* Store the API key in WordPress, `wp-config.php`, or an environment variable.
* Do not expose the key in HTML, REST responses, logs, or browser tools.
* Use administrator-confirmed live tests only.
* Prefer local conversation state unless provider storage is explicitly enabled.

