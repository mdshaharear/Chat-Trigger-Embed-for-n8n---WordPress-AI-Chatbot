export type DynamicOption = {
	label: string;
	cleanLabel: string;
};

const OPTION_PATTERN = /\[\[OPTION:([^\]\r\n]{1,80})\]\]/g;
const LEAD_STATUS_PATTERN = /\[\[LEAD_STATUS:(hot|warm|cold)\]\]/gi;

function stripControlCharacters(value: string): string {
	let output = '';
	for (const character of value) {
		const code = character.charCodeAt(0);
		if (code >= 0x20 && code !== 0x7f) {
			output += character;
		}
	}
	return output;
}

function sanitizeLabel(value: string): string {
	const cleaned = stripControlCharacters(value).trim();
	if (!cleaned) {
		return '';
	}

	if (/^(javascript|data):/i.test(cleaned)) {
		return '';
	}

	return cleaned.slice(0, 80);
}

export function parseDynamicOptions(input: string): { text: string; options: DynamicOption[] } {
	if (!input) {
		return { text: '', options: [] };
	}

	const matches = [...input.matchAll(OPTION_PATTERN)];
	if (matches.length < 2 || matches.length > 6) {
		return { text: input, options: [] };
	}

	const options = matches
		.map((match) => ({
			label: match[1],
			cleanLabel: sanitizeLabel(match[1])
		}))
		.filter((option) => option.cleanLabel.length > 0);

	if (options.length !== matches.length) {
		return { text: input, options: [] };
	}

	const stripped = input.replace(OPTION_PATTERN, '').replace(LEAD_STATUS_PATTERN, '').replace(/\n{3,}/g, '\n\n').trim();
	return { text: stripped, options };
}

export function isDynamicOptionMessage(input: string): boolean {
	return parseDynamicOptions(input).options.length > 0;
}

export function parseLeadStatus(input: string): { text: string; status: 'hot' | 'warm' | 'cold' | null } {
	const match = LEAD_STATUS_PATTERN.exec(input);
	LEAD_STATUS_PATTERN.lastIndex = 0;
	return {
		text: input.replace(LEAD_STATUS_PATTERN, '').replace(/\n{3,}/g, '\n\n').trim(),
		status: match ? (match[1].toLowerCase() as 'hot' | 'warm' | 'cold') : null
	};
}
