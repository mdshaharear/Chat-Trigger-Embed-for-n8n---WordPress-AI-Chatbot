import { expect, test } from '@playwright/test';

test('WordPress admin and plugin menu are reachable', async ({ page }) => {
	await page.goto('/wp-admin/');

	await expect(page).toHaveTitle(/Dashboard/i);
	await expect(page.locator('#toplevel_page_cten-dashboard > a')).toBeVisible();
	await expect(page.locator('#toplevel_page_cten-dashboard > a')).toContainText('AI Chat Builder');
});

test('native admin pages load without fatal errors', async ({ browser }) => {
	test.info().setTimeout(180_000);
	const pages = [
		'cten-dashboard',
		'cten-chatbots',
		'cten-ai-providers',
		'cten-n8n-actions',
		'cten-conversations',
		'cten-leads',
		'cten-usage',
		'cten-templates',
		'cten-appearance',
		'cten-analytics',
		'cten-runtime-lab',
		'cten-diagnostics',
		'cten-settings',
		'cten-tools',
		'cten-legacy-n8n',
	];

	for (const pageId of pages) {
		const page = await browser.newPage();
		try {
			await page.goto(`/wp-admin/admin.php?page=${pageId}`, { waitUntil: 'commit', timeout: 15_000 });
		} catch {
			// Some admin placeholders abort the navigation after the first response; keep checking the rendered content.
		}
		await expect(page.locator('body')).toBeVisible();
		const bodyText = await page.locator('body').innerText();
		expect(bodyText).not.toMatch(/Fatal error|Parse error|Uncaught/i);
		await page.close();
	}
});
