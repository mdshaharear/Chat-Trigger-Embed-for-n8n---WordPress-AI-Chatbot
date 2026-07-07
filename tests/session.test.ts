import { describe, expect, it, vi } from 'vitest';
import { JSDOM } from 'jsdom';
import { generateSessionId, normalizeSessionId } from '../src/public/session';

describe('session helpers', () => {
	it('normalizes safe session IDs', () => {
		expect(normalizeSessionId('abc-123-def')).toBe('abc-123-def');
		expect(normalizeSessionId('')).toBeNull();
		expect(normalizeSessionId('a<script>')).toBeNull();
	});

	it('generates a UUID-like session id', () => {
		const originalCrypto = globalThis.crypto;
		vi.stubGlobal('crypto', {
			randomUUID: () => '11111111-2222-4333-8444-555555555555'
		} as unknown as Crypto);

		expect(generateSessionId()).toBe('11111111-2222-4333-8444-555555555555');
		vi.stubGlobal('crypto', originalCrypto);
	});

	it('rotates expired stored sessions', async () => {
		const { getBrowserSessionId } = await import('../src/public/session');
		const dom = new JSDOM('', { url: 'https://example.com' });
		vi.stubGlobal('window', dom.window);
		vi.stubGlobal('localStorage', dom.window.localStorage);
		dom.window.localStorage.setItem('cten/sessionId', JSON.stringify({ id: 'expired-session', expiresAt: Date.now() - 1000 }));

		const sessionId = getBrowserSessionId(1);

		expect(sessionId).not.toBe('expired-session');
		expect(JSON.parse(dom.window.localStorage.getItem('cten/sessionId') || '{}').id).toBe(sessionId);
		vi.unstubAllGlobals();
	});
});
