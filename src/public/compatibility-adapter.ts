import { parseDynamicOptions, parseLeadStatus } from './dynamic-options';

export type CompatibilityAdapterConfig = {
	debugMode?: boolean;
	launchButtonLabel?: string;
	preventRapidSends?: boolean;
	minimumSendDelayMs?: number;
};

export class CompatibilityAdapter {
	private observer: MutationObserver | null = null;
	private processedMessages = new WeakSet<Element>();
	private lastSendAt = 0;

	constructor(private readonly root: HTMLElement, private readonly config: CompatibilityAdapterConfig) {}

	public start(): void {
		if (this.observer) {
			return;
		}

		this.scan();
		this.observer = new MutationObserver(() => this.scan());
		this.observer.observe(this.root, { childList: true, subtree: true });
	}

	public stop(): void {
		this.observer?.disconnect();
		this.observer = null;
	}

	public scan(): void {
		this.root.querySelectorAll('.chat-message').forEach((message) => {
			if (this.processedMessages.has(message)) {
				return;
			}

			const botBody = message.querySelector<HTMLElement>('.chat-message-markdown');
			if (!botBody) {
				return;
			}

			const lead = parseLeadStatus(botBody.textContent || '');
			if (lead.status) {
				message.setAttribute('data-cten-lead-status', lead.status);
				botBody.textContent = lead.text;
			}

			const parsed = parseDynamicOptions(botBody.textContent || '');
			if (!parsed.options.length) {
				this.processedMessages.add(message);
				return;
			}

			botBody.textContent = parsed.text;
			const messageNode = botBody.closest('.chat-message');
			if (!messageNode) {
				return;
			}
			const wrapper = document.createElement('div');
			wrapper.className = 'cten-dynamic-options';
			parsed.options.forEach((option, index) => {
				const button = document.createElement('button');
				button.type = 'button';
				button.className = 'cten-dynamic-options__button';
				button.textContent = option.cleanLabel;
				button.setAttribute('aria-label', option.cleanLabel);
				button.dataset.ctenOptionIndex = String(index);
				button.addEventListener('click', () => this.handleSelection(wrapper, button, option.cleanLabel));
				wrapper.appendChild(button);
			});

			messageNode.appendChild(wrapper);
			this.processedMessages.add(messageNode);
		});
	}

	private handleSelection(wrapper: HTMLElement, button: HTMLButtonElement, value: string): void {
		if (button.disabled || wrapper.dataset.ctenSelected === '1') {
			return;
		}

		wrapper.dataset.ctenSelected = '1';
		wrapper.querySelectorAll('button').forEach((sibling) => {
			sibling.disabled = true;
		});
		button.classList.add('is-selected');
		button.setAttribute('aria-pressed', 'true');
		void this.sendOption(value);
	}

	private async sendOption(value: string): Promise<void> {
		if (!this.canSendNow()) {
			this.log('Dynamic option ignored because rapid-send protection is active.');
			return;
		}

		const textarea = this.root.querySelector<HTMLTextAreaElement>('.chat-input textarea');
		const sendButton = this.root.querySelector<HTMLButtonElement>('.chat-input-send-button');
		if (!textarea || !sendButton) {
			this.log('Dynamic options unavailable: composer missing.');
			return;
		}

		textarea.value = value;
		const EventConstructor = textarea.ownerDocument.defaultView?.Event ?? Event;
		textarea.dispatchEvent(new EventConstructor('input', { bubbles: true }));
		sendButton.click();
	}

	private log(message: string): void {
		if (this.config.debugMode) {
			console.debug('[cten]', message);
		}
	}

	private canSendNow(): boolean {
		if (!this.config.preventRapidSends) {
			return true;
		}

		const now = Date.now();
		const delay = Math.max(250, this.config.minimumSendDelayMs ?? 900);
		if (now - this.lastSendAt < delay) {
			return false;
		}

		this.lastSendAt = now;
		return true;
	}
}
