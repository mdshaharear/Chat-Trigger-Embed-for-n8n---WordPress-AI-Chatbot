# n8n Chat Compatibility

Internal-use release note: this compatibility summary applies to the bundled `@n8n/chat` assets shipped with the plugin. The upstream n8n bundle and notices retain their original licenses.

Installed package version: `@n8n/chat@1.26.0`

Package metadata:

* Exports `createChat` from the package entry
* Ships `dist/chat.bundle.es.js`, `dist/chat.bundle.umd.js`, `dist/chat.es.js`, `dist/chat.umd.js`, and `dist/style.css`
* Uses Vue 3 internally

Supported `createChat` options from the package types:

* `webhookUrl`
* `webhookConfig.method`
* `webhookConfig.headers`
* `target`
* `mode`
* `showWindowCloseButton`
* `showWelcomeScreen`
* `loadPreviousSession`
* `sessionId`
* `chatInputKey`
* `chatSessionKey`
* `defaultLanguage`
* `initialMessages`
* `messageHistory`
* `metadata`
* `i18n`
* `theme`
* `messageComponents`
* `disabled`
* `allowFileUploads`
* `allowedFilesMimeTypes`
* `enableStreaming`
* `beforeMessageSent`
* `afterMessageSent`
* `enableMessageActions`

Supported modes:

* `window`
* `fullscreen`

Supported localization details:

* `defaultLanguage` is `en`
* `i18n` is keyed by language code
* The documented English fields include `title`, `subtitle`, `footer`, `getStarted`, `inputPlaceholder`, and `closeButtonTooltip`

Session behavior:

* The package stores session state in localStorage namespace `n8n-chat`
* The session ID key is `n8n-chat/sessionId`
* Requests are sent with `action=sendMessage` and `action=loadPreviousSession`

Webhook behavior:

* `sendMessage` sends the user prompt to the production webhook
* `loadPreviousSession` restores prior context when enabled
* Streaming requires workflow-side streaming configuration

Metadata behavior:

* The plugin passes only public metadata
* Browser-derived fields such as language, timezone, title, URL, and referrer are merged client-side
* The payload avoids WordPress admin data, user emails, passwords, and cookies

CSS variables exposed by the packaged stylesheet include, among others:

* `--chat--color--primary`
* `--chat--color--secondary`
* `--chat--window--width`
* `--chat--window--height`
* `--chat--header--background`
* `--chat--message--bot--background`
* `--chat--message--user--background`
* `--chat--input--background`
* `--chat--toggle--size`
* `--chat--body--background`

Known limitations:

* The package only documents English localization in this version
* There is no documented public imperative send API on the `createChat()` return value
* Quick actions therefore use a compatibility adapter that interacts with the embedded composer safely in one place

Compatibility adapter details:

* One adapter handles composer lookup
* The selectors are isolated in the frontend controller
* The adapter falls back safely if the package DOM changes
* Debug logging is gated by plugin debug mode
