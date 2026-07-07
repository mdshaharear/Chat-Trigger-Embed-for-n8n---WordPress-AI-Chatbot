# Mock n8n Contract

This project embeds `@n8n/chat@1.26.0`. A local mock was specified for Phase 8, but no browser/WordPress runtime was available in this environment to execute it end-to-end.

Standalone mock server:

```bash
node tests/mock-n8n-server.mjs
```

Default endpoint:

```text
http://localhost:8787/webhook/chat
```

## Request Shape

The official runtime sends requests to the configured `webhookUrl`.

For `sendMessage`:

```json
{
  "action": "sendMessage",
  "sessionId": "browser-session-id",
  "chatInput": "visitor message",
  "metadata": {
    "pluginVersion": "2.0.0",
    "pagePath": "/example",
    "preChat": {},
    "leadQualification": {}
  }
}
```

For `loadPreviousSession`:

```json
{
  "action": "loadPreviousSession",
  "sessionId": "browser-session-id",
  "metadata": {
    "pluginVersion": "2.0.0"
  }
}
```

`chatInput` and `sessionId` keys are configurable through plugin settings.

## Responses to Cover

Mock coverage should include:

* Plain text assistant response.
* JSON object response supported by `@n8n/chat`.
* Array/history response for `loadPreviousSession`.
* Empty response.
* Invalid JSON.
* Streaming chunks when streaming is enabled.
* HTTP `400`, `401`, `403`, `404`, `429`, and `500`.
* Timeout and network failure.

## Plugin-Specific Markers

Assistant text may include:

```text
[[OPTION:Customer Support]]
[[OPTION:Lead Collection]]
[[LEAD_STATUS:hot]]
```

The compatibility adapter must hide valid markers from visible text, render valid options once, and ignore invalid lead statuses.
