export function buildPublicMetadata(fields: Record<string, boolean> = {}): Record<string, unknown> {
	const metadata: Record<string, unknown> = {};
	if (fields.page_title) {
		metadata.pageTitle = document.title;
	}
	if (fields.page_url) {
		metadata.pageUrl = window.location.origin + window.location.pathname;
	}
	if (fields.page_path) {
		metadata.pagePath = window.location.pathname;
	}
	if (fields.referrer) {
		metadata.referrer = document.referrer || '';
	}
	if (fields.browser_lang) {
		metadata.browserLanguage = navigator.language || 'en';
	}
	if (fields.browser_tz) {
		metadata.browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
	}
	return metadata;
}

export function mergePublicMetadata(base: Record<string, unknown> = {}, config: Record<string, unknown> = {}, fields: Record<string, boolean> = {}): Record<string, unknown> {
	return {
		...base,
		...config,
		...buildPublicMetadata(fields)
	};
}
