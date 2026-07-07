import { afterEach, describe, expect, it, vi } from 'vitest';
import { JSDOM } from 'jsdom';
import { CompatibilityAdapter } from '../src/public/compatibility-adapter';

describe('CompatibilityAdapter', () => {
	afterEach(() => {
		vi.unstubAllGlobals();
	});

	it('renders safe buttons for valid dynamic option markers', () => {
		const dom = new JSDOM(
			`<div id="root"><div class="chat-message"><div class="chat-message-markdown">Hello [[OPTION:Customer Support]] [[OPTION:Lead Collection]]</div></div><div class="chat-input"><textarea></textarea><button class="chat-input-send-button"></button></div></div>`,
			{ url: 'https://example.com' }
		);

		const { window } = dom;
		vi.stubGlobal('window', window);
		vi.stubGlobal('document', window.document);
		vi.stubGlobal('MutationObserver', window.MutationObserver);

		const root = window.document.getElementById('root') as HTMLElement;
		const adapter = new CompatibilityAdapter(root, { debugMode: false });
		adapter.scan();

		expect(root.querySelectorAll('.cten-dynamic-options__button')).toHaveLength(2);
		expect(root.querySelector('.chat-message-markdown')?.textContent).toBe('Hello');
		expect(root.querySelector('.chat-message')?.textContent).toContain('Customer Support');
	});

	it('prevents rapid duplicate dynamic option sends', () => {
		const dom = new JSDOM(
			`<div id="root"><div class="chat-message"><div class="chat-message-markdown">Hello [[OPTION:One]] [[OPTION:Two]]</div></div><div class="chat-input"><textarea></textarea><button class="chat-input-send-button"></button></div></div>`,
			{ url: 'https://example.com' }
		);

		const { window } = dom;
		vi.stubGlobal('window', window);
		vi.stubGlobal('document', window.document);
		vi.stubGlobal('MutationObserver', window.MutationObserver);

		const root = window.document.getElementById('root') as HTMLElement;
		const sendButton = root.querySelector<HTMLButtonElement>('.chat-input-send-button');
		const clickSpy = vi.spyOn(sendButton as HTMLButtonElement, 'click');
		const adapter = new CompatibilityAdapter(root, { preventRapidSends: true, minimumSendDelayMs: 1000 });
		adapter.scan();

		const buttons = root.querySelectorAll<HTMLButtonElement>('.cten-dynamic-options__button');
		buttons[0]?.click();
		buttons[1]?.click();

		expect(clickSpy).toHaveBeenCalledTimes(1);
	});
});
