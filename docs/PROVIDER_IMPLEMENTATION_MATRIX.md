# Provider Implementation Matrix

This matrix tracks the shared provider contract used by the native AI gateway.

| Provider | `validate_configuration()` | `test_connection()` | `send_message()` | `normalize_response()` | Notes |
| --- | --- | --- | --- | --- | --- |
| OpenAI | Checks for an API key and reports configuration errors early. | Posts a lightweight Responses API request. | Uses `POST /v1/responses`. | Parses output text, structured options, and usage. | Supports native AI mode. |
| Gemini | Checks for an API key and reports configuration errors early. | Sends a lightweight `generateContent` request. | Uses the Gemini Generative Language API. | Extracts candidate text, synthetic options, and token usage. | Supports native AI mode. |
| n8n | Validates the stored Chat Trigger URL and blocks unsafe targets. | Posts a small connection-test payload to the saved webhook URL. | Posts the chat message payload to the saved webhook URL. | Normalizes JSON and plain-text responses into the shared response object. | This is the production n8n bridge. |
| Legacy n8n | Always passes configuration validation for backward compatibility. | Returns a compatibility-only success response. | Returns a legacy-mode error instead of bridging the old runtime path. | Provides a compatibility fallback response object. | Kept so older settings can still load safely. |
| Mock | Always passes. | Always passes. | Returns a deterministic canned response. | Passes through mock responses for test harnesses. | Used for internal checks and offline smoke tests. |

## Contract notes

- `validate_configuration()` should fail fast on missing credentials, malformed URLs, or unsafe destinations.
- `test_connection()` may perform a network request, but only after configuration validation has passed.
- `normalize_response()` should convert raw transport responses into `AI_Response` instances with a stable error payload when transport or parsing fails.
- The native runtime always prefers the provider-specific `send_message()` implementation and only falls back to the mock provider when no matching provider is available.
