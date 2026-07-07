# Native AI Privacy

WordPress may forward configured chatbot content to the selected provider.

Important points:

* API keys remain server-side.
* Conversation storage is optional.
* Lead storage is optional.
* Provider-side storage is off by default unless explicitly enabled.
* Retention, deletion, and consent remain the administrator's responsibility.

Suggested policy text:

* We may send chatbot messages and optional metadata to the configured AI provider or workflow.
* We do not intentionally expose secrets in the browser.
* We retain data only as configured by the site administrator.

