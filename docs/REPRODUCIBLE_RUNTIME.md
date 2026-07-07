# Reproducible Runtime

This phase uses WordPress Playground as the primary reproducible runtime.

## Toolchain

- Node.js `22.23.1`
- npm `10.9.8`
- PHP `8.3.31`
- Browser automation: Playwright with the locally installed Google Chrome executable

## Playground targets

Use the current repository as the Playground project root and start one of these servers:

```powershell
# Mounted plugin runtime for admin/public browser checks
powershell -ExecutionPolicy Bypass -File tools/runtime/start-playground.ps1 -ProjectRoot (Get-Location).Path -Port 9400

# Fresh site for real ZIP upload testing (resets the cached Playground site first)
powershell -ExecutionPolicy Bypass -File tools/runtime/start-playground-fresh.ps1 -ProjectRoot (Get-Location).Path -Port 9401
```

The mounted runtime automatically exposes the plugin through the local repository.
The fresh runtime starts a clean WordPress site with no auto-mounted plugin files so the ZIP uploader can be exercised.

## Browser tests

Set the browser executable and base URL before running Playwright:

```powershell
$env:PLAYWRIGHT_BASE_URL = 'http://127.0.0.1:9400'
$env:PLAYWRIGHT_CHROME_PATH = 'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe'
npm run test:e2e
```

For ZIP-upload tests, point Playwright at the fresh runtime:

```powershell
$env:PLAYWRIGHT_BASE_URL = 'http://127.0.0.1:9401'
$env:PLAYWRIGHT_CHROME_PATH = 'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe'
npm run test:e2e
```

## Notes

- The Playground CLI stores site files under `%USERPROFILE%\.wordpress-playground\sites`.
- Debug logging and browser inspection are available from the Playground server logs and the Playwright trace/video artifacts when tests fail.
- Do not commit the Playground site directory, browser recordings, or test artifacts into the release ZIP.
