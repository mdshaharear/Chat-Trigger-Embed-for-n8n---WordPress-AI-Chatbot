export type RuntimeEventName =
	| 'Controller Loaded'
	| 'Vendor Runtime Requested'
	| 'Vendor Runtime Loaded'
	| 'Runtime Initialized'
	| 'Launcher Rendered'
	| 'Chat Opened'
	| 'Chat Closed'
	| 'Message Submitted'
	| 'Assistant Response Rendered'
	| 'Quick Action Clicked'
	| 'Dynamic Option Rendered'
	| 'Dynamic Option Clicked'
	| 'Error Displayed'
	| 'Retry Clicked'
	| 'Session Restored';

export type RuntimeTestConfig = {
	enabled?: boolean;
	token?: string;
	endpoint?: string;
	expiresAt?: string;
};

export function createTemporaryRuntimeToken(): string {
	const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
	let token = '';
	const bytes = new Uint8Array(12);
	if (typeof crypto !== 'undefined' && typeof crypto.getRandomValues === 'function') {
		crypto.getRandomValues(bytes);
	} else {
		for (let index = 0; index < bytes.length; index += 1) {
			bytes[index] = Math.floor(Math.random() * 255);
		}
	}
	for (const byte of bytes) {
		token += chars[byte % chars.length];
	}
	return token;
}

export function sanitizeRuntimeEventName(value: string): RuntimeEventName | null {
	const allowed = new Set<RuntimeEventName>([
		'Controller Loaded',
		'Vendor Runtime Requested',
		'Vendor Runtime Loaded',
		'Runtime Initialized',
		'Launcher Rendered',
		'Chat Opened',
		'Chat Closed',
		'Message Submitted',
		'Assistant Response Rendered',
		'Quick Action Clicked',
		'Dynamic Option Rendered',
		'Dynamic Option Clicked',
		'Error Displayed',
		'Retry Clicked',
		'Session Restored'
	]);
	return allowed.has(value as RuntimeEventName) ? (value as RuntimeEventName) : null;
}

export class RuntimeEventCollector {
	constructor(private readonly config: RuntimeTestConfig) {}

	public isEnabled(): boolean {
		return Boolean(this.config.enabled && this.config.token && this.config.endpoint);
	}

	public emit(name: RuntimeEventName): void {
		if (!this.isEnabled()) {
			return;
		}

		const eventName = sanitizeRuntimeEventName(name);
		if (!eventName) {
			return;
		}

		const body = JSON.stringify({
			event: eventName,
			token: this.config.token
		});

		if (navigator.sendBeacon) {
			navigator.sendBeacon(this.config.endpoint || '', body);
			return;
		}

		void fetch(this.config.endpoint || '', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body
		});
	}
}
