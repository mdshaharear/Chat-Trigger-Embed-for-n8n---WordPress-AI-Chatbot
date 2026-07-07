import { describe, expect, it } from 'vitest';
import { readFileSync, statSync } from 'node:fs';
import { resolve } from 'node:path';

describe('release assets', () => {
	it('keeps generated public assets present and non-empty', () => {
		const js = statSync(resolve('dist/chat-trigger-embed.js'));
		const css = statSync(resolve('dist/chat-trigger-embed.css'));

		const vendor = statSync(resolve('dist/vendor/n8n-chat/chat.bundle.es.js'));
		const cssText = readFileSync(resolve('dist/chat-trigger-embed.css'), 'utf8');

		expect(js.size).toBeGreaterThan(5_000);
		expect(css.size).toBeGreaterThan(2_000);
		expect(vendor.size).toBeGreaterThan(1_000_000);
		expect(cssText).toContain('.cten-lazy-launcher');
		expect(cssText).toContain('.cten-quick-actions__button');
		expect(cssText).toContain('.cten-dynamic-options__button');
		expect(cssText).toContain('.cten-pre-chat');
		expect(cssText).toContain('.cten-contact-fallback');
	});
});
