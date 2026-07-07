# Native AI Architecture

The intended request flow is:

Browser Native Chat UI -> WordPress Public AI Gateway -> Provider Adapter -> OpenAI, Gemini, n8n, or Hybrid -> Normalized Response -> Native Chat UI

Key rules:

* API keys stay server-side.
* The browser never calls OpenAI or Gemini directly.
* Legacy n8n embed remains available for backward compatibility.
* Provider-specific raw responses stay on the server.

