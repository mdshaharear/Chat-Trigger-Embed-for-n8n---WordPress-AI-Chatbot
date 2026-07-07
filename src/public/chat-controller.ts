import './styles.css';
import { readConfig, type CtenChatConfig, type CtenChatLanguageConfig } from './chat-config';
import { applyResponsiveVars } from './responsive';
import { normalizeQuickActions } from './quick-actions';
import { mergePublicMetadata } from './metadata';
import { CompatibilityAdapter } from './compatibility-adapter';
import { getBrowserSessionId, hasStoredSession, rotateBrowserSessionId } from './session';
import { RuntimeEventCollector, type RuntimeTestConfig } from './runtime-events';
import { initNativeChat } from './native-chat';

type ChatState = Window & {
	__ctenInitialized?: boolean;
	__ctenAdapter?: CompatibilityAdapter | null;
	__ctenConfig?: CtenChatConfig | null;
	__ctenRestart?: () => void;
	__ctenLastSendAt?: number;
	__ctenEvents?: RuntimeEventCollector | null;
};

let runtimePromise: Promise<typeof import('@n8n/chat')> | null = null;

function loadRuntime(): Promise<typeof import('@n8n/chat')> {
	runtimePromise ??= import('@n8n/chat');
	return runtimePromise;
}

function canSendNow(config: CtenChatConfig, state: ChatState): boolean {
	if (!config.preventRapidSends) {
		return true;
	}

	const now = Date.now();
	const delay = Math.max(250, config.minimumSendDelayMs ?? 900);
	if (state.__ctenLastSendAt && now - state.__ctenLastSendAt < delay) {
		return false;
	}

	state.__ctenLastSendAt = now;
	return true;
}

function getChatRoot(): HTMLElement {
	let root = document.getElementById('cten-chat-root');
	if (!root) {
		root = document.createElement('div');
		root.id = 'cten-chat-root';
		root.className = 'n8n-chat cten-chat';
		document.body.appendChild(root);
	}

	return root;
}

function applyWidgetAttrs(root: HTMLElement, config: CtenChatConfig): void {
	if (config.launcherPosition) {
		root.dataset.ctenLauncherPosition = config.launcherPosition;
	}
	if (config.mobileLayout) {
		root.dataset.ctenMobileLayout = config.mobileLayout;
	}
	if (config.themeMode) {
		root.dataset.ctenThemeMode = config.themeMode;
	}
	root.dataset.ctenShowOnlineIndicator = config.showOnlineIndicator === false ? '0' : '1';
	if (config.desktopWidth) {
		root.style.setProperty('--chat--window--width', `${config.desktopWidth}px`);
	}
	if (config.desktopHeight) {
		root.style.setProperty('--chat--window--height', `${config.desktopHeight}px`);
	}
	if (config.launcherSize) {
		root.style.setProperty('--chat--toggle--size', `${config.launcherSize}px`);
	}
}

function applyQuickActionPanel(config: CtenChatConfig, root: HTMLElement, state: ChatState): void {
	const actions = normalizeQuickActions(config.quickActions ?? []);
	const host = document.getElementById('cten-quick-actions');
	if (!host) {
		return;
	}

	host.replaceChildren();
	if (!actions.length) {
		return;
	}

	host.classList.add('is-visible');

	const heading = document.createElement('div');
	heading.className = 'cten-quick-actions__title';
	heading.textContent = config.i18n?.en?.getStarted || 'New Conversation';
	host.appendChild(heading);

	const list = document.createElement('div');
	list.className = 'cten-quick-actions__list';
	host.appendChild(list);

	const createComposerController = (value: string): boolean => {
		const textarea = root.querySelector<HTMLTextAreaElement>('.chat-input textarea');
		const sendButton = root.querySelector<HTMLButtonElement>('.chat-input-send-button');
		if (!textarea || !sendButton) {
			return false;
		}

		textarea.value = value;
		textarea.dispatchEvent(new Event('input', { bubbles: true }));
		sendButton.click();
		return true;
	};

	actions.forEach((action) => {
		const button = document.createElement('button');
		button.type = 'button';
		button.className = 'cten-quick-actions__button';
		button.textContent = action.label;
		button.setAttribute('aria-label', action.label);
		button.addEventListener('click', () => {
			if (button.disabled) {
				return;
			}

			button.disabled = true;
			state.__ctenEvents?.emit('Quick Action Clicked');
		const sent = canSendNow(config, state) && createComposerController(action.message || action.label);
			if (!sent) {
				button.disabled = false;
				return;
			}

			host.classList.add('is-hidden');
		});
		list.appendChild(button);
	});
}

function applyStartNewConversation(root: HTMLElement, state: ChatState, config: CtenChatConfig): void {
	let control = document.getElementById('cten-start-new-conversation') as HTMLButtonElement | null;
	if (!control) {
		control = document.createElement('button');
		control.type = 'button';
		control.id = 'cten-start-new-conversation';
		control.className = 'cten-start-new-conversation';
	}
	control.textContent = config.newConversationText || 'Start New Conversation';

	const reset = (): void => {
		const hasConversation = root.querySelector('.chat-message');
		if (config.confirmNewConversation !== false && hasConversation && !window.confirm('Start a new conversation?')) {
			return;
		}

		rotateBrowserSessionId(config.sessionExpiryDays);
		state.__ctenEvents?.emit('Chat Closed');
		window.location.reload();
	};

	control.removeEventListener('click', reset);
	control.addEventListener('click', reset);
	root.appendChild(control);

	state.__ctenRestart = reset;
}

function applyBehaviourGuards(root: HTMLElement, state: ChatState, config: CtenChatConfig): void {
	root.addEventListener('click', (event) => {
		const target = event.target;
		if (!(target instanceof HTMLElement) || !target.closest('.chat-input-send-button')) {
			return;
		}
		if (!canSendNow(config, state)) {
			event.preventDefault();
			event.stopPropagation();
		}
	}, true);

	root.addEventListener('input', (event) => {
		const target = event.target;
		if (!(target instanceof HTMLTextAreaElement) || !config.maxInputLength) {
			return;
		}
		if (target.value.length > config.maxInputLength) {
			target.value = target.value.slice(0, config.maxInputLength);
			target.dispatchEvent(new Event('input', { bubbles: true }));
		}
	});

	if (config.closeWithEscape !== false) {
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				root.querySelector<HTMLButtonElement>('.chat-window-toggle')?.focus();
			}
		});
	}
}

function applyFallbackLinks(config: CtenChatConfig, root: HTMLElement): void {
	const fallback = config.contactFallback;
	if (!fallback || (!fallback.whatsappUrl && !fallback.emailUrl && !fallback.contactPageUrl)) {
		return;
	}

	const host = document.createElement('div');
	host.className = 'cten-contact-fallback';
	const links = [
		fallback.whatsappEnabled && fallback.whatsappUrl ? fallback.whatsappUrl : '',
		fallback.emailEnabled && fallback.emailUrl ? fallback.emailUrl : '',
		fallback.contactPageEnabled && fallback.contactPageUrl ? fallback.contactPageUrl : ''
	].filter(Boolean);

	links.forEach((url) => {
		const link = document.createElement('a');
		link.href = url;
		link.rel = 'noopener noreferrer';
		link.target = url.startsWith('mailto:') ? '' : '_blank';
		link.textContent = fallback.label || 'Contact support';
		host.appendChild(link);
	});

	root.appendChild(host);
}

function renderPreChatForm(config: CtenChatConfig, root: HTMLElement, onComplete: (values: Record<string, string>) => void): void {
	void config;
	void root;
	onComplete({});
}

function createLazyLauncher(config: CtenChatConfig, root: HTMLElement, start: () => void): HTMLButtonElement {
	const button = document.createElement('button');
	button.type = 'button';
	button.className = 'cten-lazy-launcher';
		button.textContent = config.i18n?.en?.title || config.launcherAccessibilityLabel || 'Chat';
		button.setAttribute('aria-label', config.launcherAccessibilityLabel || 'Open chatbot');
		button.addEventListener('click', start, { once: true });
	if (config.preloadOnHover) {
		button.addEventListener('pointerenter', () => void loadRuntime(), { once: true });
		button.addEventListener('focus', () => void loadRuntime(), { once: true });
	}
	root.appendChild(button);
	return button;
}

export function initChat(): void {
	const state = window as ChatState;
	const config = readConfig();
	if (!config || state.__ctenInitialized) {
		return;
	}

	state.__ctenInitialized = true;
	state.__ctenConfig = config;
	state.__ctenEvents = new RuntimeEventCollector((config.runtimeTest ?? {}) as RuntimeTestConfig);
	state.__ctenEvents.emit('Controller Loaded');

	if (config.mode === 'native' && config.chatbot && config.restUrl) {
		void initNativeChat({
			...config,
			mode: 'native',
			restUrl: config.restUrl,
			chatbot: config.chatbot
		});
		return;
	}

	const root = getChatRoot();
	if (config.appearance) {
		applyResponsiveVars(root, config.appearance);
	}
	applyWidgetAttrs(root, config);
	applyBehaviourGuards(root, state, config);
	applyFallbackLinks(config, root);

	if (!config.webhookUrl) {
		return;
	}

	const startRuntime = (_preChatValues: Record<string, string>): void => {
		state.__ctenEvents?.emit('Vendor Runtime Requested');
		if (hasStoredSession()) {
			state.__ctenEvents?.emit('Session Restored');
		}
		const sessionId = getBrowserSessionId(config.sessionExpiryDays);
		const adapter = new CompatibilityAdapter(root, {
			debugMode: config.debugMode,
			launchButtonLabel: config.launcherAccessibilityLabel,
			preventRapidSends: config.preventRapidSends,
			minimumSendDelayMs: config.minimumSendDelayMs
		});
		adapter.start();
		state.__ctenAdapter = adapter;

		void loadRuntime().then(({ createChat }) => {
			state.__ctenEvents?.emit('Vendor Runtime Loaded');
			createChat({
				webhookUrl: config.webhookUrl,
				webhookConfig: config.webhookConfig,
				target: '#cten-chat-root',
				mode: 'window',
				showWelcomeScreen: false,
				loadPreviousSession: Boolean(config.loadPreviousSession),
				sessionId,
				chatInputKey: config.chatInputKey,
				chatSessionKey: config.chatSessionKey,
				enableStreaming: Boolean(config.enableStreaming),
				initialMessages: config.initialMessages,
				metadata: mergePublicMetadata(config.metadata ?? {}, { pluginVersion: config.pluginVersion || '2.0.0', leadQualification: config.leadQualification ?? {} }, config.metadataFields ?? {}),
				defaultLanguage: 'en',
				i18n: config.i18n as Record<string, CtenChatLanguageConfig> | undefined,
				allowFileUploads: false,
				showWindowCloseButton: true,
				enableMessageActions: true
			});

			applyQuickActionPanel(config, root, state);
			applyStartNewConversation(root, state, config);
			state.__ctenEvents?.emit('Runtime Initialized');
			if (config.autoOpenEnabled) {
				window.setTimeout(() => root.querySelector<HTMLButtonElement>('.chat-window-toggle')?.click(), Math.max(0, config.autoOpenDelaySeconds ?? 8) * 1000);
			}
		}).catch(() => {
			root.dataset.ctenRuntimeError = '1';
			state.__ctenEvents?.emit('Error Displayed');
			SafeModeFallback();
		});
	};

	const begin = (): void => renderPreChatForm(config, root, startRuntime);
	const delayMs = Math.max(config.launcherDelaySeconds ?? 0, config.loadAfterDelaySeconds ?? 0) * 1000;

	if (config.lazyLoadRuntime) {
		const launcher = createLazyLauncher(config, root, () => {
			launcher.remove();
			begin();
		});
		state.__ctenEvents?.emit('Launcher Rendered');
		if (delayMs > 0) {
			launcher.hidden = true;
			window.setTimeout(() => {
				launcher.hidden = false;
			}, delayMs);
		}
		if (config.autoOpenEnabled) {
			window.setTimeout(() => launcher.click(), Math.max(0, config.autoOpenDelaySeconds ?? 8) * 1000);
		}
		return;
	}

	if (delayMs > 0) {
		window.setTimeout(begin, delayMs);
		return;
	}

	begin();
}

function SafeModeFallback(): void {
	// The admin Runtime Lab can inspect this flag when the runtime fails to initialize.
	document.body.dataset.ctenRuntimeFailed = '1';
}

document.addEventListener('DOMContentLoaded', initChat);
