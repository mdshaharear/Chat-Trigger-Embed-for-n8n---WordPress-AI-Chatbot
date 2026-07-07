import { expect, test } from '@playwright/test';
import { resolve } from 'node:path';

const zipPath = resolve(process.cwd(), 'dist/chat-trigger-embed-for-n8n.zip');

test('release ZIP installs and activates on a fresh WordPress Playground site', async ({ page }) => {
	await page.goto('/wp-admin/plugin-install.php?tab=upload');
	await expect(page.locator('body')).toContainText(/Upload Plugin/i);

	await page.setInputFiles('input[type="file"]', zipPath);
	await page.getByRole('button', { name: /Install Now/i }).click();

	const body = page.locator('body');
	await expect(body).toContainText(/Plugin installed successfully|This plugin is already installed|Replace current with uploaded/i, { timeout: 60_000 });

	const activateLink = page.getByRole('link', { name: /Activate Plugin/i }).first();
	if (await activateLink.isVisible()) {
		await activateLink.click({ force: true });
		await expect(body).toContainText(/Plugin activated|Activated/i, { timeout: 60_000 });
	}

	await page.goto('/wp-admin/plugins.php');
	await expect(page.locator('body')).toContainText(/AI Chat Builder for WordPress - OpenAI, Gemini & n8n/i);
	await expect(page.locator('body')).toContainText(/Version 2\.0\.0/i);
	await expect(page.locator('body')).toContainText(/Deactivate/i);
});
