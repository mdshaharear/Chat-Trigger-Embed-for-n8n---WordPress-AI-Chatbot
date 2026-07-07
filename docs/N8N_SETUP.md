# n8n Setup

Open-source release note: bundled n8n runtime files keep their original licenses.

Use the n8n **Chat Trigger** node with these settings:

* Make Chat Publicly Available: enabled
* Chat mode: Embedded Chat
* Workflow state: Active
* Allowed Origins: add the exact WordPress origin, such as `https://example.com`
* Load Previous Session: From Memory when Redis Chat Memory or another supported memory node is connected
* Streaming response: enable only if you plan to use streaming responses

Use the **production webhook URL** from the Chat Trigger node. Do not paste a test webhook URL.

Testing steps:

1. Save the plugin connection settings.
2. Open the WordPress site on the public frontend.
3. Send a test message.
4. Confirm the workflow receives `action=sendMessage`.
5. Reopen the chat and confirm session loading sends `action=loadPreviousSession`.

Common CORS errors usually mean:

* The WordPress origin is missing from Allowed Origins
* The workflow is inactive
* The URL is a test endpoint instead of the production endpoint

Streaming compatibility:

* The plugin can enable streaming in its public config.
* The workflow must also be configured for streaming responses in n8n.
