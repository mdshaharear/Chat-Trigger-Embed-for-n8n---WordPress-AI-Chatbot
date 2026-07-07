import { expect, test } from '@playwright/test';

async function enablePublicChat(page) {
	await page.goto('/wp-admin/admin.php?page=cten-settings');
	await page.locator('#cten_enabled').check();
	await page.getByLabel('n8n Chat Trigger Production URL').fill('https://example.com/webhook');
	await page.getByRole('button', { name: 'Save Connection' }).click();
	await expect(page.locator('.notice-success, .notice-info, .updated, .notice')).toContainText(/Settings saved/i);
}

async function createMockConnection(page) {
	const connectionName = `Mock Connection ${Date.now()}`;
	await page.goto('/wp-admin/admin.php?page=cten-ai-providers');
	await page.getByLabel('Connection Name').fill(connectionName);
	await page.getByLabel('Provider', { exact: true }).selectOption('mock');
	await page.getByLabel('Secret Storage').selectOption('none');
	await page.getByLabel('Secret / Value').fill('mock-sentinel');
	await page.getByLabel('Default Model').fill('mock-chat-mini');
	await page.getByLabel('Timeout (seconds)').fill('30');
	await page.locator('#connection_cten_enabled').check();
	await page.getByRole('button', { name: 'Create Connection' }).click({ force: true });
	await expect(page.locator('.notice-success, .notice-info, .updated, .notice')).toContainText(/Saved connection/i);
	await expect(page.getByRole('cell', { name: connectionName })).toBeVisible();
	return connectionName;
}

async function createMockChatbot(page, connectionName) {
	const chatbotName = `Mock Chatbot ${Date.now()}`;
	await page.goto('/wp-admin/admin.php?page=cten-chatbots', { waitUntil: 'commit', timeout: 15_000 });
	await page.getByLabel('Chatbot Name').fill(chatbotName);
	await page.getByLabel('Internal Name').fill(`mock-chatbot-${Date.now()}`);
	await page.getByLabel('Engine').selectOption('mock');
	await page.getByLabel('Provider Connection').selectOption({ label: connectionName });
	await page.getByLabel('Model ID').fill('mock-chat-mini');
	await page.getByLabel('System Instructions').fill('Reply plainly.');
	await page.getByLabel('Welcome Message').fill('Hello from mock.');
	await page.getByLabel('Input Placeholder').fill('Ask me anything');
	await page.getByLabel('Error Message').fill('Mock error');
	await page.getByLabel('Fallback Message').fill('Fallback');
	await page.getByLabel('Theme Preset').selectOption('clean-light');
	await page.getByLabel('Launcher Label').fill('Chat now');
	await page.getByLabel('Visibility').selectOption('entire_site');
	await page.getByLabel('Selected Page IDs').fill('');
	await page.getByLabel('Max input characters').fill('1000');
	await page.getByLabel('Max output tokens').fill('128');
	await page.getByLabel('Messages per session').fill('10');
	await page.getByLabel('Requests per minute').fill('20');
	await page.getByLabel('Daily request limit').fill('0');
	await page.locator('#chatbot_cten_enabled').check();
	await page.getByRole('button', { name: 'Create Chatbot' }).click({ force: true });
	await expect(page.getByRole('cell', { name: chatbotName })).toBeVisible();
}

test('mock provider admin flow can create a chatbot and expose the public shell', async ({ page }) => {
	await enablePublicChat(page);
	const connectionName = await createMockConnection(page);
	await createMockChatbot(page, connectionName);

	await page.goto('/');
	await expect(page.locator('#cten-chat-shell')).toHaveCount(1);
	await expect(page.locator('#cten-chat-root')).toHaveCount(1);
	const configJson = await page.locator('#cten-chat-config').evaluate((node) => node.innerHTML || '');
	expect(configJson).toContain('&quot;mode&quot;:&quot;native&quot;');
	expect(configJson).toContain('&quot;welcomeMessage&quot;:&quot;Hello from mock.&quot;');
	await expect(page.locator('body')).not.toContainText(/Fatal error|Parse error|Uncaught/i);
});
