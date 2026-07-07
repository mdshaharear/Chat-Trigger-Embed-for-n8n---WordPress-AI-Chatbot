import { describe, expect, it } from 'vitest';
import { normalizeQuickActions } from '../src/public/quick-actions';

describe('normalizeQuickActions', () => {
	it('sorts enabled actions and skips disabled entries', () => {
		const actions = normalizeQuickActions([
			{ enabled: true, label: 'B', message: 'b', icon: 'b', sort: 20 },
			{ enabled: false, label: 'A', message: 'a', icon: 'a', sort: 10 },
			{ enabled: true, label: 'A', message: 'a', icon: 'a', sort: 10 }
		]);

		expect(actions).toHaveLength(2);
		expect(actions[0]?.label).toBe('A');
		expect(actions[1]?.label).toBe('B');
	});

	it('drops empty labels and provides icon defaults', () => {
		const actions = normalizeQuickActions([
			{ enabled: true, label: '   ', message: 'x', icon: '', sort: 'x' as unknown as number }
		]);

		expect(actions).toHaveLength(0);
	});

	it('keeps a professional manager sized list sorted predictably', () => {
		const actions = normalizeQuickActions(
			Array.from({ length: 12 }, (_, index) => ({
				enabled: true,
				label: `Action ${12 - index}`,
				message: `Message ${index}`,
				icon: '',
				sort: 12 - index
			}))
		);

		expect(actions).toHaveLength(12);
		expect(actions[0]?.label).toBe('Action 1');
	});
});
