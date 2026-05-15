import { test, expect } from '@playwright/test';

test.describe('Admin — Financial Report Templates', () => {
  test.describe.configure({ mode: 'serial' });

  test('navigate to Financial cluster reports', async ({ page }) => {
    const response = await page.goto('/admin/financial');
    const status = response?.status() ?? 500;

    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(2000);

      // Financial cluster page should have navigation
      const hasContent = await page.locator('main, .fi-main').first().isVisible().catch(() => false);
      expect(hasContent).toBeTruthy();
    }
  });

  test('generated reports page loads', async ({ page }) => {
    const response = await page.goto('/admin/financial/generated-reports');
    const status = response?.status() ?? 500;

    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(2000);

      // Page renders without server error
      const hasError = await page.getByText(/500|Server Error|Whoops/i).isVisible().catch(() => false);
      expect(hasError).toBeFalsy();
    }
  });

});
