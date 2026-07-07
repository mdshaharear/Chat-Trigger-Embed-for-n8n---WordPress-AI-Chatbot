# Local Toolchain

This repo is validated with local portable toolchains so the release checks do not depend on the legacy Node 8 runtime that may appear first on `PATH`.

## Installed toolchain

- Node.js `v22.23.1`
- npm `10.9.8`
- PHP `8.3.31` NTS x64

## Local paths

- Node: `C:\CodexToolchain\node-v22.23.1-win-x64\node.exe`
- npm: `C:\CodexToolchain\node-v22.23.1-win-x64\npm.cmd`
- PHP: `C:\CodexToolchain\php-8.3.31-nts-Win32-vs16-x64\php.exe`

## Environment variables

- `CTEN_NODE_BIN` points the verifier to the Node binary above.
- `CTEN_PHP_BIN` points the verifier to the PHP binary above.

## Verification commands

Run the full release pass with:

```powershell
$env:CTEN_NODE_BIN = 'C:\CodexToolchain\node-v22.23.1-win-x64\node.exe'
$env:CTEN_PHP_BIN = 'C:\CodexToolchain\php-8.3.31-nts-Win32-vs16-x64\php.exe'
node scripts/verify-build.mjs
```

If you only need the standard JavaScript checks, use:

```powershell
npm ci
npm run build
npm run lint
npm run typecheck
npm test
```

## Notes

- The portable Node install is required because the machine also has an older Adobe-bundled Node 8 executable on `PATH`.
- PHP lint is included because the plugin still ships a substantial PHP surface even though the automated test suite is primarily JavaScript.
