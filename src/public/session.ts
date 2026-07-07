const STORAGE_KEY = 'cten/sessionId';

type StoredSession = {
	id: string;
	expiresAt: number;
};

export function getStorage(): Storage | null {
	try {
		return window.localStorage;
	} catch {
		return null;
	}
}

export function generateSessionId(): string {
	if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
		return crypto.randomUUID();
	}

	const bytes = new Uint8Array(16);
	if (typeof crypto !== 'undefined' && typeof crypto.getRandomValues === 'function') {
		crypto.getRandomValues(bytes);
	} else {
		for (let index = 0; index < bytes.length; index += 1) {
			bytes[index] = Math.floor(Math.random() * 256);
		}
	}

	bytes[6] = (bytes[6] & 0x0f) | 0x40;
	bytes[8] = (bytes[8] & 0x3f) | 0x80;

	return [...bytes]
		.map((byte, index) => {
			const hex = byte.toString(16).padStart(2, '0');
			if (index === 3 || index === 5 || index === 7 || index === 9) {
				return `${hex}-`;
			}
			return hex;
		})
		.join('');
}

export function normalizeSessionId(raw: unknown): string | null {
	if (typeof raw !== 'string') {
		return null;
	}

	const value = raw.trim();
	return /^[A-Za-z0-9-]{8,128}$/.test(value) ? value : null;
}

function parseStoredSession(raw: string | null): StoredSession | null {
	const legacy = normalizeSessionId(raw);
	if (legacy) {
		return { id: legacy, expiresAt: 0 };
	}

	try {
		const parsed = JSON.parse(raw || '{}') as Partial<StoredSession>;
		const id = normalizeSessionId(parsed.id);
		const expiresAt = Number(parsed.expiresAt);
		if (!id || !Number.isFinite(expiresAt)) {
			return null;
		}
		return { id, expiresAt };
	} catch {
		return null;
	}
}

export function hasStoredSession(): boolean {
	const storage = getStorage();
	const stored = storage ? parseStoredSession(storage.getItem(STORAGE_KEY)) : null;
	return Boolean(stored && (!stored.expiresAt || stored.expiresAt > Date.now()));
}

function persistSession(storage: Storage | null, id: string, expiryDays = 30): void {
	if (!storage) {
		return;
	}

	const ttl = Math.max(1, Math.min(180, expiryDays)) * 24 * 60 * 60 * 1000;
	storage.setItem(STORAGE_KEY, JSON.stringify({ id, expiresAt: Date.now() + ttl }));
}

export function getBrowserSessionId(expiryDays = 30): string {
	const storage = getStorage();
	const existing = storage ? parseStoredSession(storage.getItem(STORAGE_KEY)) : null;
	if (existing && (!existing.expiresAt || existing.expiresAt > Date.now())) {
		if (!existing.expiresAt) {
			persistSession(storage, existing.id, expiryDays);
		}
		return existing.id;
	}

	const sessionId = generateSessionId();
	persistSession(storage, sessionId, expiryDays);

	return sessionId;
}

export function rotateBrowserSessionId(expiryDays = 30): string {
	const sessionId = generateSessionId();
	persistSession(getStorage(), sessionId, expiryDays);

	return sessionId;
}

export function clearBrowserSessionId(): void {
	const storage = getStorage();
	if (storage) {
		storage.removeItem(STORAGE_KEY);
	}
}
