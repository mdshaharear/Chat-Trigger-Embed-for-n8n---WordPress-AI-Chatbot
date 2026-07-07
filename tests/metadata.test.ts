import { afterEach, describe, expect, it, vi } from 'vitest';
import { JSDOM } from 'jsdom';
import { buildPublicMetadata, mergePublicMetadata } from '../src/public/metadata';

describe('metadata helpers', () => {
	afterEach(() => {
		vi.unstubAllGlobals();
	});

	it('only emits browser metadata fields that are explicitly enabled', () => {
		const dom = new JSDOM('<title>Safe Page</title>', { url: 'https://example.com/demo?token=secret&utm_source=newsletter' });
		vi.stubGlobal('window', dom.window);
		vi.stubGlobal('document', dom.window.document);
		vi.stubGlobal('navigator', dom.window.navigator);

		const metadata = buildPublicMetadata({ page_title: true, page_url: true, browser_lang: false });

		expect(metadata.pageTitle).toBe('Safe Page');
		expect(metadata.pageUrl).toBe('https://example.com/demo');
		expect(metadata.browserLanguage).toBeUndefined();
	});

	it('merges server metadata without re-adding disabled browser fields', () => {
		const dom = new JSDOM('<title>Safe Page</title>', { url: 'https://example.com/demo' });
		vi.stubGlobal('window', dom.window);
		vi.stubGlobal('document', dom.window.document);
		vi.stubGlobal('navigator', dom.window.navigator);

		const metadata = mergePublicMetadata({ pluginVersion: '1.3.0' }, {}, { page_path: true });

		expect(metadata.pluginVersion).toBe('1.3.0');
		expect(metadata.pagePath).toBe('/demo');
		expect(metadata.pageTitle).toBeUndefined();
	});
});
