export type CtenChatConfig = {
	mode?: 'legacy_n8n' | 'native';
	chatbotId?: string;
	chatbotName?: string;
	chatbot?: NativeChatbotConfig;
	restUrl?: string;
	welcomeMessage?: string;
	inputPlaceholder?: string;
	errorMessage?: string;
	staticFallbackMessage?: string;
	launcherLabel?: string;
	maximumInputCharacters?: number;
	maximumOutputTokens?: number;
	messagesPerSession?: number;
	requestsPerMinute?: number;
	dailyRequestLimit?: number;
	webhookUrl: string;
	webhookConfig?: { method?: 'GET' | 'POST'; headers?: Record<string, string> };
	pluginVersion?: string;
	chatInputKey?: string;
	chatSessionKey?: string;
	loadPreviousSession?: boolean;
	enableStreaming?: boolean;
	initialMessages?: string[];
	metadata?: Record<string, unknown>;
	metadataFields?: Record<string, boolean>;
	i18n?: Record<string, Record<string, string>>;
	quickActions?: Array<{ enabled: boolean; label: string; message: string; icon: string; sort: number }>;
	appearance?: Record<string, string>;
	debugMode?: boolean;
	offlineErrorText?: string;
	launcherAccessibilityLabel?: string;
	launcherPosition?: string;
	launcherSize?: number;
	mobileLayout?: string;
	desktopWidth?: number;
	desktopHeight?: number;
	tabletWidth?: number;
	tabletHeight?: number;
	launcherDelaySeconds?: number;
	autoOpenEnabled?: boolean;
	autoOpenDelaySeconds?: number;
	closeOnOutsideClick?: boolean;
	preventRapidSends?: boolean;
	minimumSendDelayMs?: number;
	maxInputLength?: number;
	confirmNewConversation?: boolean;
	closeWithEscape?: boolean;
	lazyLoadRuntime?: boolean;
	preloadOnHover?: boolean;
	loadAfterDelaySeconds?: number;
	showOnlineIndicator?: boolean;
	sessionExpiryDays?: number;
	runtimeTest?: {
		enabled?: boolean;
		token?: string;
		endpoint?: string;
		expiresAt?: string;
	};
	newConversationText?: string;
	contactFallback?: {
		whatsappEnabled?: boolean;
		whatsappUrl?: string;
		emailEnabled?: boolean;
		emailUrl?: string;
		contactPageEnabled?: boolean;
		contactPageUrl?: string;
		label?: string;
	};
	preChatForm?: {
		enabled?: boolean;
		sending?: 'metadata';
		allow_skip?: boolean;
		privacy_text?: string;
		fields?: Array<{
			key: string;
			type: 'text' | 'email' | 'phone' | 'url' | 'select' | 'consent';
			enabled: boolean;
			required: boolean;
			label: string;
			placeholder?: string;
			help?: string;
			sort?: number;
			options?: string[];
		}>;
	};
	leadQualification?: Record<string, unknown>;
};

export type NativeChatbotConfig = {
	id: string;
	name: string;
	internalName: string;
	enabled: boolean;
	engine: 'openai' | 'gemini' | 'n8n' | 'mock';
	uiMode: 'native';
	providerConnectionId: string;
	modelId: string;
	systemInstructions: string;
	welcomeMessage: string;
	inputPlaceholder: string;
	errorMessage: string;
	staticFallbackMessage: string;
	quickActions: Array<{ id: string; enabled: boolean; label: string; message: string; sort: number }>;
	themePreset: string;
	launcherLabel: string;
	pageVisibilityMode: 'entire_site' | 'homepage' | 'selected_pages' | 'excluded_pages';
	selectedPageIds: number[];
	maximumInputCharacters: number;
	maximumOutputTokens: number;
	messagesPerSession: number;
	requestsPerMinute: number;
	dailyRequestLimit: number;
};

export type CtenChatLanguageConfig = {
	title: string;
	subtitle: string;
	footer: string;
	getStarted: string;
	inputPlaceholder: string;
	closeButtonTooltip: string;
	retryButton: string;
};

export function readConfig(): CtenChatConfig | null {
	const node = document.getElementById('cten-chat-config');
	if (!node) {
		return null;
	}

	try {
		return JSON.parse(node.textContent || '{}') as CtenChatConfig;
	} catch {
		return null;
	}
}
