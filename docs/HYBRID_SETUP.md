# Hybrid Setup

Hybrid mode uses a native AI provider for conversation plus explicit allowlisted n8n actions.

Rules:

* Tool IDs are allowlisted.
* Arguments are validated server-side.
* No arbitrary URLs, code execution, or destructive tool calls without confirmation.
* Limit tool-call loops and duplicate calls.

