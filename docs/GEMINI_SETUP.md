# Gemini Setup

Gemini support in Phase 8 follows the same server-side security model as OpenAI.

Requirements:

* Keep keys out of the browser.
* Use explicit administrator confirmation for live tests.
* Normalize provider-specific response shapes before they reach the frontend.
* Mask any key-bearing URLs from diagnostics and error output.

