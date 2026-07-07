import { normalizeQuickActions } from './quick-actions';
import { createTemporaryRuntimeToken, RuntimeEventCollector, type RuntimeTestConfig } from './runtime-events';
import { getBrowserSessionId, clearBrowserSessionId, rotateBrowserSessionId } from './session';
import type { CtenChatConfig, NativeChatbotConfig } from './chat-config';

type NativeChatMessage = {
	id: string;
	role: 'user' | 'assistant' | 'error';
	content: string;
	options?: Array<{ label: string; value: string }>;
};

type NativeChatRuntimeConfig = CtenChatConfig & {
	mode: 'native';
	restUrl: string;
	chatbot: NativeChatbotConfig;
};

type NativeState = Window & {
	__ctenNative?: {
		open: boolean;
		sessionId: string;
		messages: NativeChatMessage[];
		lastRequestId: string;
		lastUserMessage: string;
		lastSelection: { label: string; value: string } | null;
		events: RuntimeEventCollector | null;
	};
};

const DEFAULT_MAX_MESSAGES = 50;

function storage(): Storage | null {
	try {
		return window.localStorage;
	} catch {
		return null;
	}
}

function getStorageKey(chatbotId: string, suffix: string): string {
	return `cten/native/${chatbotId}/${suffix}`;
}

function readJson<T>(key: string, fallback: T): T {
	try {
		const raw = storage()?.getItem(key);
		if (!raw) {
			return fallback;
		}
		return JSON.parse(raw) as T;
	} catch {
		return fallback;
	}
}

function writeJson(key: string, value: unknown): void {
	try {
		storage()?.setItem(key, JSON.stringify(value));
	} catch {
		// Ignore storage errors.
	}
}

function createNode<K extends keyof HTMLElementTagNameMap>(tag: K, className?: string, text?: string): HTMLElementTagNameMap[K] {
	const node = document.createElement(tag);
	if (className) {
		node.className = className;
	}
	if (typeof text === 'string') {
		node.textContent = text;
	}
	return node;
}

function renderRichText(text: string): DocumentFragment {
	const fragment = document.createDocumentFragment();
	const blocks = text.split(/\n{2,}/);
	blocks.forEach((block, index) => {
		const trimmed = block.trim();
		if (trimmed.startsWith('```')) {
			const pre = createNode('pre', 'cten-native-message__code');
			const code = createNode('code');
			code.textContent = trimmed.replace(/^```[a-z]*\n?/i, '').replace(/```$/, '');
			pre.appendChild(code);
			fragment.appendChild(pre);
		} else if (trimmed) {
			const paragraph = createNode('p');
			const parts = trimmed.split(/(`[^`]+`|\*\*[^*]+\*\*|\*[^*]+\*|\[[^\]]+\]\([^)]+\))/g);
			parts.forEach((part) => {
				if (!part) {
					return;
				}
				if (part.startsWith('`') && part.endsWith('`')) {
					const code = createNode('code');
					code.textContent = part.slice(1, -1);
					paragraph.appendChild(code);
					return;
				}
				if (part.startsWith('**') && part.endsWith('**')) {
					const strong = createNode('strong');
					strong.textContent = part.slice(2, -2);
					paragraph.appendChild(strong);
					return;
				}
				if (part.startsWith('*') && part.endsWith('*')) {
					const em = createNode('em');
					em.textContent = part.slice(1, -1);
					paragraph.appendChild(em);
					return;
				}
				const linkMatch = /^\[([^\]]+)\]\(([^)]+)\)$/.exec(part);
				if (linkMatch) {
					const href = linkMatch[2].trim();
					if (!/^(https?:|mailto:|tel:)/i.test(href)) {
						paragraph.appendChild(document.createTextNode(linkMatch[1]));
						return;
					}
					const anchor = createNode('a') as HTMLAnchorElement;
					anchor.href = href;
					anchor.rel = 'noopener noreferrer';
					anchor.target = href.startsWith('mailto:') || href.startsWith('tel:') ? '' : '_blank';
					anchor.textContent = linkMatch[1];
					paragraph.appendChild(anchor);
					return;
				}
				paragraph.appendChild(document.createTextNode(part));
			});
			fragment.appendChild(paragraph);
		}
		if (index < blocks.length - 1) {
			fragment.appendChild(document.createElement('br'));
		}
	});
	return fragment;
}

function sanitizeMessage(message: string): string {
	return message.replace(/\[\[(OPTION|LEAD_STATUS):[^\]]+\]\]/g, '').trim();
}

function loadState(chatbotId: string): { sessionId: string; open: boolean; messages: NativeChatMessage[]; selected: { label: string; value: string } | null } {
	return {
		sessionId: readJson<string>(getStorageKey(chatbotId, 'session'), ''),
		open: readJson<boolean>(getStorageKey(chatbotId, 'open'), false),
		messages: readJson<NativeChatMessage[]>(getStorageKey(chatbotId, 'messages'), []),
		selected: readJson<{ label: string; value: string } | null>(getStorageKey(chatbotId, 'selected'), null)
	};
}

function saveState(
	chatbotId: string,
	state: { sessionId: string; open: boolean; messages: NativeChatMessage[]; selected: { label: string; value: string } | null }
): void {
	writeJson(getStorageKey(chatbotId, 'session'), state.sessionId);
	writeJson(getStorageKey(chatbotId, 'open'), state.open);
	writeJson(getStorageKey(chatbotId, 'messages'), state.messages.slice(-DEFAULT_MAX_MESSAGES));
	writeJson(getStorageKey(chatbotId, 'selected'), state.selected);
}

function createMessage(role: NativeChatMessage['role'], content: string, options: NativeChatMessage['options'] = []): NativeChatMessage {
	return {
		id: `${role}-${Date.now()}-${Math.random().toString(16).slice(2)}`,
		role,
		content,
		options
	};
}

async function postMessage(config: NativeChatRuntimeConfig, payload: Record<string, unknown>): Promise<Record<string, unknown>> {
	const response = await fetch(config.restUrl, {
		method: 'POST',
		credentials: 'same-origin',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify(payload)
	});

	const text = await response.text();
	let parsed: Record<string, unknown> = {};
	try {
		parsed = text ? (JSON.parse(text) as Record<string, unknown>) : {};
	} catch {
		parsed = { message: text };
	}

	if (!response.ok && !parsed.error) {
		parsed.error = {
			code: 'http_error',
			visitor_message: (parsed.message as string) || `Request failed (${response.status}).`,
			retryable: response.status >= 500
		};
	}

	return parsed;
}

function renderMessages(list: HTMLElement, messages: NativeChatMessage[], events: RuntimeEventCollector | null): void {
	list.replaceChildren();
	messages.forEach((message) => {
		const item = createNode('div', `cten-native-message cten-native-message--${message.role}`);
		const bubble = createNode('div', 'cten-native-message__bubble');
		bubble.appendChild(renderRichText(message.content || ''));
		item.appendChild(bubble);
		if (message.options?.length) {
			const options = createNode('div', 'cten-native-options');
			message.options.forEach((option) => {
				const button = createNode('button', 'cten-native-options__button', option.label) as HTMLButtonElement;
				button.type = 'button';
				button.dataset.value = option.value;
				options.appendChild(button);
			});
			item.appendChild(options);
			events?.emit('Dynamic Option Rendered');
		}
		list.appendChild(item);
	});
	list.scrollTop = list.scrollHeight;
}

function ensureWelcomeMessage(messages: NativeChatMessage[], welcomeMessage: string): NativeChatMessage[] {
	if (messages.length || !welcomeMessage.trim()) {
		return messages;
	}

	return [createMessage('assistant', welcomeMessage)];
}

export async function initNativeChat(config: NativeChatRuntimeConfig): Promise<void> {
	const state = window as NativeState;
	const root = document.getElementById('cten-chat-root');
	const chatbot = config.chatbot;
	if (!root || state.__ctenNative || !chatbot) {
		return;
	}

	const chatbotId = config.chatbotId || chatbot.id;
	const chatbotName = config.chatbotName || chatbot.name;
	const welcomeMessage = config.welcomeMessage || chatbot.welcomeMessage || '';
	const inputPlaceholder = config.inputPlaceholder || chatbot.inputPlaceholder || 'Type a message...';
	const errorMessage = config.errorMessage || chatbot.errorMessage || 'Something went wrong.';
	const staticFallbackMessage = config.staticFallbackMessage || chatbot.staticFallbackMessage || '';
	const launcherLabel = config.launcherLabel || chatbotName || chatbot.name;
	const maximumInputCharacters = config.maximumInputCharacters || chatbot.maximumInputCharacters || 1000;

	const storageState = loadState(chatbotId);
	const sessionId = storageState.sessionId || getBrowserSessionId(config.sessionExpiryDays);
	const events = config.runtimeTest ? new RuntimeEventCollector(config.runtimeTest as RuntimeTestConfig) : null;
	const messages = ensureWelcomeMessage(storageState.messages, welcomeMessage);

	state.__ctenNative = {
		open: storageState.open,
		sessionId,
		messages,
		lastRequestId: '',
		lastUserMessage: '',
		lastSelection: storageState.selected,
		events
	};

	root.innerHTML = '';
	root.dataset.ctenMode = 'native';
	root.classList.add('cten-native-root');

	const launcher = createNode('button', 'cten-native-launcher', launcherLabel) as HTMLButtonElement;
	launcher.type = 'button';
	launcher.setAttribute('aria-label', launcherLabel);

	const panel = createNode('section', 'cten-native-panel');
	const header = createNode('header', 'cten-native-header');
	const titleWrap = createNode('div', 'cten-native-header__titles');
	titleWrap.append(
		createNode('div', 'cten-native-header__title', chatbotName),
		createNode('div', 'cten-native-header__subtitle', config.i18n?.en?.subtitle || '')
	);
	const closeButton = createNode('button', 'cten-native-close', '×') as HTMLButtonElement;
	closeButton.type = 'button';
	closeButton.setAttribute('aria-label', config.i18n?.en?.closeButtonTooltip || 'Close chat');
	header.append(titleWrap, closeButton);

	const welcome = createNode('div', 'cten-native-welcome', welcomeMessage);
	const messagesHost = createNode('div', 'cten-native-messages');
	const quickActions = createNode('div', 'cten-native-quick-actions');
	const composer = createNode('form', 'cten-native-composer') as HTMLFormElement;
	composer.setAttribute('novalidate', 'novalidate');
	const input = createNode('textarea', 'cten-native-input') as HTMLTextAreaElement;
	input.placeholder = inputPlaceholder;
	input.maxLength = maximumInputCharacters;
	const send = createNode('button', 'cten-native-send', 'Send') as HTMLButtonElement;
	send.type = 'submit';
	const footer = createNode('div', 'cten-native-footer');
	const retry = createNode('button', 'cten-native-retry', config.i18n?.en?.retryButton || 'Retry') as HTMLButtonElement;
	retry.type = 'button';
	const startNew = createNode('button', 'cten-native-new', config.newConversationText || 'New conversation') as HTMLButtonElement;
	startNew.type = 'button';
	const privacy = createNode('div', 'cten-native-privacy', config.i18n?.en?.footer || 'Messages are handled by the selected provider.');
	const error = createNode('div', 'cten-native-error');

	footer.append(retry, startNew, privacy);
	composer.append(input, send);
	panel.append(header, welcome, quickActions, messagesHost, composer, error, footer);
	root.append(launcher, panel);

	const setOpen = (open: boolean, emit = true): void => {
		state.__ctenNative!.open = open;
		panel.hidden = !open;
		launcher.hidden = open;
		saveState(chatbotId, {
			sessionId: state.__ctenNative!.sessionId,
			open,
			messages: state.__ctenNative!.messages,
			selected: state.__ctenNative!.lastSelection
		});
		if (!emit) {
			return;
		}
		if (open) {
			events?.emit('Chat Opened');
		} else {
			events?.emit('Chat Closed');
		}
	};

	const showError = (message: string): void => {
		error.textContent = message;
		error.hidden = !message;
		if (message) {
			events?.emit('Error Displayed');
		}
	};

	const appendAssistant = (payload: { message: string; options?: Array<{ label: string; value: string }> }): void => {
		state.__ctenNative!.messages.push(createMessage('assistant', sanitizeMessage(payload.message), payload.options ?? []));
		renderMessages(messagesHost, state.__ctenNative!.messages, events);
		saveState(chatbotId, {
			sessionId: state.__ctenNative!.sessionId,
			open: true,
			messages: state.__ctenNative!.messages,
			selected: state.__ctenNative!.lastSelection
		});
		events?.emit('Assistant Response Rendered');
	};

	const sendPayload = async (message: string, selection: { label: string; value: string } | null = null): Promise<void> => {
		const clean = message.trim().slice(0, maximumInputCharacters);
		if (!clean) {
			return;
		}

		state.__ctenNative!.lastUserMessage = clean;
		state.__ctenNative!.lastSelection = selection;
		state.__ctenNative!.messages.push(createMessage('user', clean));
		renderMessages(messagesHost, state.__ctenNative!.messages, events);
		saveState(chatbotId, {
			sessionId: state.__ctenNative!.sessionId,
			open: true,
			messages: state.__ctenNative!.messages,
			selected: selection
		});
		events?.emit('Message Submitted');

		send.disabled = true;
		input.disabled = true;
		showError('');

		const requestId = `${createTemporaryRuntimeToken()}-${Date.now().toString(16)}`;
		state.__ctenNative!.lastRequestId = requestId;

		try {
			const reply = await postMessage(config, {
				chatbot_id: chatbotId,
				session_id: state.__ctenNative!.sessionId,
				request_id: requestId,
				message: clean,
				history: state.__ctenNative!.messages.slice(-12).map((item) => ({ role: item.role, content: item.content })),
				selection,
				metadata: { pageUrl: window.location.href, chatbotName },
				honeypot: ''
			});

			if (reply.error) {
				const errorPayload = reply.error as Record<string, unknown>;
				const visitorMessage = String(errorPayload.visitor_message || errorMessage || 'Something went wrong.');
				showError(visitorMessage);
				state.__ctenNative!.messages.push(createMessage('error', visitorMessage));
				renderMessages(messagesHost, state.__ctenNative!.messages, events);
				return;
			}

	appendAssistant({
				message: String(reply.message || staticFallbackMessage || ''),
			options: Array.isArray(reply.options) ? (reply.options as Array<{ label: string; value: string }>) : []
		});
		} catch {
			const fallback = errorMessage || 'Unable to send message. Please try again.';
			showError(fallback);
		} finally {
			send.disabled = false;
			input.disabled = false;
			input.focus();
		}
	};

	const resetConversation = (): void => {
		state.__ctenNative!.sessionId = rotateBrowserSessionId(config.sessionExpiryDays);
		state.__ctenNative!.messages = ensureWelcomeMessage([], welcomeMessage);
		state.__ctenNative!.lastSelection = null;
		clearBrowserSessionId();
		renderMessages(messagesHost, state.__ctenNative!.messages, events);
		showError('');
		saveState(chatbotId, {
			sessionId: state.__ctenNative!.sessionId,
			open: true,
			messages: state.__ctenNative!.messages,
			selected: null
		});
		events?.emit('Chat Closed');
		setOpen(true);
	};

	launcher.addEventListener('click', () => {
		events?.emit('Launcher Rendered');
		setOpen(true);
		input.focus();
	});
	closeButton.addEventListener('click', () => setOpen(false));
	retry.addEventListener('click', () => {
		events?.emit('Retry Clicked');
		void sendPayload(state.__ctenNative!.lastUserMessage, state.__ctenNative!.lastSelection);
	});
	startNew.addEventListener('click', resetConversation);
	composer.addEventListener('submit', (event) => {
		event.preventDefault();
		void sendPayload(input.value, state.__ctenNative!.lastSelection);
		input.value = '';
	});

	quickActions.replaceChildren();
	normalizeQuickActions(config.quickActions ? (config.quickActions as Array<{ enabled: boolean; label: string; message: string; icon: string; sort: number }>) : []).forEach((action) => {
		const button = createNode('button', 'cten-native-quick-actions__button', action.label) as HTMLButtonElement;
		button.type = 'button';
		button.addEventListener('click', () => {
			events?.emit('Quick Action Clicked');
			void sendPayload(action.message || action.label);
		});
		quickActions.appendChild(button);
	});

	messagesHost.addEventListener('click', (event) => {
		const target = event.target;
		if (!(target instanceof HTMLButtonElement) || !target.dataset.value) {
			return;
		}
		const selection = { label: target.textContent || target.dataset.value, value: target.dataset.value };
		events?.emit('Dynamic Option Clicked');
		void sendPayload(target.dataset.value, selection);
	});

	renderMessages(messagesHost, state.__ctenNative!.messages, events);
	setOpen(Boolean(storageState.open), false);
	events?.emit('Runtime Initialized');
}
