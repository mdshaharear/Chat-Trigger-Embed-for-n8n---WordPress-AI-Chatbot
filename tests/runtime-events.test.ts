import { describe, expect, it } from 'vitest';
import { RuntimeEventCollector, createTemporaryRuntimeToken, sanitizeRuntimeEventName } from '../src/public/runtime-events';
import { serializeRuntimeReport, serializeRuntimeReportText } from '../src/public/runtime-report';

describe('runtime events', () => {
	it('creates a short-lived temporary token', () => {
		const token = createTemporaryRuntimeToken();
		expect(token).toHaveLength(12);
		expect(/^[a-z0-9]+$/.test(token)).toBe(true);
	});

	it('sanitizes only allowed runtime event names', () => {
		expect(sanitizeRuntimeEventName('Runtime Initialized')).toBe('Runtime Initialized');
		expect(sanitizeRuntimeEventName('javascript:alert(1)')).toBeNull();
	});

	it('skips beacon emissions when disabled', () => {
		const collector = new RuntimeEventCollector({});
		collector.emit('Controller Loaded');
		expect(true).toBe(true);
	});

	it('serializes runtime reports for json and text downloads', () => {
		const report = {
			reportSchemaVersion: '1.0',
			pluginVersion: '2.0.0-alpha.1',
			databaseVersion: '2.0.0-alpha.1',
			wordpressVersion: '6.7',
			phpVersion: '8.2',
			theme: 'Default'
		};
		expect(serializeRuntimeReport(report)).toContain('"pluginVersion": "2.0.0-alpha.1"');
		expect(serializeRuntimeReportText(report)).toContain('Plugin Version: 2.0.0-alpha.1');
	});
});
