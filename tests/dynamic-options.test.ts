import { describe, expect, it } from 'vitest';
import { isDynamicOptionMessage, parseDynamicOptions, parseLeadStatus } from '../src/public/dynamic-options';

describe('parseDynamicOptions', () => {
	it('parses valid markers and strips them from visible text', () => {
		const result = parseDynamicOptions('Hello\n[[OPTION:Customer Support]]\n[[OPTION:Lead Collection]]');
		expect(result.options).toHaveLength(2);
		expect(result.text).toBe('Hello');
		expect(result.options[0]?.cleanLabel).toBe('Customer Support');
	});

	it('rejects numbered lists', () => {
		const result = parseDynamicOptions('1. First\n2. Second');
		expect(result.options).toHaveLength(0);
		expect(result.text).toContain('1. First');
		expect(isDynamicOptionMessage('1. First\n2. Second')).toBe(false);
	});

	it('rejects malformed or unsafe labels', () => {
		const result = parseDynamicOptions('[[OPTION:javascript:alert(1)]]\n[[OPTION:Bad]]');
		expect(result.options).toHaveLength(0);
	});

	it('strips safe lead status markers without exposing invalid statuses', () => {
		expect(parseLeadStatus('Thanks [[LEAD_STATUS:hot]]')).toEqual({ text: 'Thanks', status: 'hot' });
		expect(parseLeadStatus('No marker [[LEAD_STATUS:admin]]')).toEqual({ text: 'No marker [[LEAD_STATUS:admin]]', status: null });
	});
});
