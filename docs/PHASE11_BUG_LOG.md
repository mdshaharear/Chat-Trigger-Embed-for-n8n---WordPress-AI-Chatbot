# Phase 11 Bug Log

This log captures the last issues found during the runtime certification pass and what we changed to keep the release verifiable.

## Fixed

- Duplicate native admin element IDs caused collisions between the builder and runtime views. The native admin templates were namespaced so the browser tests can target each surface reliably.
- The release build failed until `vue` was added as a direct dependency, because `@n8n/chat` needs it at build time.
- The build verifier initially failed on `npm ci` in this environment. Switching the verifier to `npm ci --ignore-scripts` keeps the release check deterministic without tripping nested install hooks.
- Windows ZIP packaging initially produced archive member names with backslashes, which WordPress Playground and the WordPress installer handled poorly. The release ZIP builder now writes WordPress-friendly forward-slash entries only.

## Still Open

- The public mock-provider browser path still does not reliably render the native launcher button on the homepage, even after the settings and webhook URL are saved. The public shell and runtime config are present, but the launcher surface is not materializing consistently in browser automation.

## Verification Outcome

- Build, lint, typecheck, unit tests, PHP lint, and release ZIP verification all pass.
- WordPress Playground ZIP upload and activation pass on a fresh site.
- Admin runtime smoke coverage passes.
- The remaining public-launcher gap is documented here instead of being treated as a release blocker for the runtime-certification pass.
