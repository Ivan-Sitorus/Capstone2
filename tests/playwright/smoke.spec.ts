import { test, expect } from '@playwright/test';

test.describe('smoke', () => {
  test('login page loads and displays correctly', async ({ page }) => {
    await page.goto('/login');
    await page.waitForSelector('#app[data-page]', { timeout: 15000 });

    const dataPage = await page.getAttribute('#app', 'data-page');
    expect(dataPage).toBeTruthy();

    if (dataPage) {
      const parsed = JSON.parse(dataPage);
      expect(parsed.component).toBe('Auth/Login');
    }

    const emailInput = page.locator('input[type="email"]');
    await expect(emailInput).toBeVisible({ timeout: 10000 });

    const passwordInput = page.locator('input[type="password"]');
    await expect(passwordInput).toBeVisible();
  });
});
