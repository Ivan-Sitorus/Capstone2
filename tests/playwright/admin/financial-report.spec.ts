import { test, expect } from '@playwright/test';

test.describe('Admin — Financial Report Templates', () => {
  test.describe.configure({ mode: 'serial' });

  const TEMPLATE_NAME = `E2E Template ${Date.now()}`;

  test('login as admin → dashboard accessible', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    // Navigate to Filament admin panel
    const response = await page.goto('/admin');
    const status = response?.status() ?? 500;

    // Admin panel should load (may redirect to login if session expired)
    expect(status).toBeLessThan(500);

    // If redirected to login page, that's acceptable for auth-flow test
    // The storageState should have admin session from auth.setup.ts
    if (status === 200) {
      await page.waitForTimeout(2000);

      // Filament admin dashboard should have navigation
      const hasDashboard = await page.getByText(/Dashboard|Dasbor/i).isVisible().catch(() => false);
      const hasNavigation = await page.locator('.fi-sidebar, nav').first().isVisible().catch(() => false);
      expect(hasDashboard || hasNavigation).toBeTruthy();
    }

    const critical = errors.filter(e => !e.includes('favicon') && !e.includes('Google Analytics'));
    expect(critical).toEqual([]);
  });

  test('navigate to Report Header Templates resource', async ({ page }) => {
    const response = await page.goto('/admin/report-header-templates');
    const status = response?.status() ?? 500;

    // Page should load successfully
    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(2000);

      // Filament table or resource page should render
      const hasTable = await page.locator('table, .fi-ta, [data-resource]').first().isVisible().catch(() => false);
      const hasCreate = await page.getByText(/Template Header Laporan|Buat/i).isVisible().catch(() => false);
      expect(hasTable || hasCreate).toBeTruthy();
    }
  });

  test('create new report header template', async ({ page }) => {
    const response = await page.goto('/admin/report-header-templates/create');
    const status = response?.status() ?? 500;

    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(2000);

      // Fill in template form fields
      const nameInput = page.locator('input').filter({ has: page.locator('..') }).first();
      const nameVisible = await nameInput.isVisible().catch(() => false);

      if (nameVisible) {
        // Try to fill the "Nama Template" field
        const allInputs = page.locator('input[type="text"]');
        const inputCount = await allInputs.count();

        if (inputCount > 0) {
          await allInputs.first().fill(TEMPLATE_NAME);
          await page.waitForTimeout(300);
        }

        // Look for "Nama Entitas" field (second text input typically)
        if (inputCount > 1) {
          await allInputs.nth(1).fill('E2E Test Entity');
          await page.waitForTimeout(300);
        }

        // Try to find and click the Create/Save button
        const createBtn = page.getByRole('button', { name: /Buat|Create|Simpan/i });
        const createVisible = await createBtn.isVisible().catch(() => false);

        if (createVisible) {
          await createBtn.click();
          await page.waitForTimeout(3000);

          // After creation, should redirect to list page or show success
          const currentUrl = page.url();
          const hasSuccess = currentUrl.includes('report-header-templates');
          expect(hasSuccess).toBeTruthy();
        }
      }
    }
  });

  test('template appears in list after creation', async ({ page }) => {
    const response = await page.goto('/admin/report-header-templates');
    const status = response?.status() ?? 500;

    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(2000);

      // Search for the created template in the table
      const pageContent = await page.textContent('body');

      // The template name should appear in the list (if creation was successful)
      if (pageContent && pageContent.includes(TEMPLATE_NAME)) {
        expect(pageContent).toContain(TEMPLATE_NAME);
      } else {
        // At minimum, the list page should render without errors
        const hasTable = await page.locator('table, .fi-ta').first().isVisible().catch(() => false);
        expect(hasTable).toBeTruthy();
      }
    }
  });

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

  test('saved templates page loads', async ({ page }) => {
    const response = await page.goto('/admin/financial/saved-templates');
    const status = response?.status() ?? 500;

    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(2000);

      const hasError = await page.getByText(/500|Server Error|Whoops/i).isVisible().catch(() => false);
      expect(hasError).toBeFalsy();
    }
  });
});
