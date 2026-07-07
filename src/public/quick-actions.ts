export type QuickAction = {
	enabled: boolean;
	label: string;
	message: string;
	icon: string;
	sort: number;
};

export type RawQuickAction = Partial<QuickAction> & Record<string, unknown>;

function toText(value: unknown): string {
	if (typeof value !== 'string') {
		return '';
	}

	let output = '';
	for (const character of value.trim()) {
		const code = character.charCodeAt(0);
		if (code >= 0x20 && code !== 0x7f) {
			output += character;
		}
	}

	return output;
}

function toSort(value: unknown, fallback: number): number {
	const numeric = Number(value);
	return Number.isFinite(numeric) ? numeric : fallback;
}

export function normalizeQuickActions(actions: RawQuickAction[]): QuickAction[] {
	const normalized = actions.map((action, index) => ({
		enabled: Boolean(action.enabled),
		label: toText(action.label),
		message: toText(action.message),
		icon: toText(action.icon) || 'sparkles',
		sort: toSort(action.sort, (index + 1) * 10)
	}));

	return normalized
		.filter((action) => action.enabled && action.label.length > 0)
		.sort((left, right) => left.sort - right.sort || left.label.localeCompare(right.label));
}
