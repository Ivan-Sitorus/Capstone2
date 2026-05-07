import { test, expect } from '@playwright/test';
import { waitForInertia } from '../helpers';

test.describe('K1 - Login Kasir', () => {
  test('renders split-screen layout: navy left panel + white form right', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/login');
    await waitForInertia(page);

    // Inertia page component
    const dataPage = await page.getAttribute('#app', 'data-page');
    expect(dataPage).toBeTruthy();
    const parsed = JSON.parse(dataPage!);
    expect(parsed.component).toBe('Auth/Login');

    // Branding on left
    await expect(page.getByText('W9 Cafe')).toBeVisible();
    await expect(page.getByText('Sistem Point of Sale')).toBeVisible();

    // Form heading on right
    await expect(page.getByText('Masuk ke Akun Anda')).toBeVisible();
    await expect(page.getByText(/Masukkan email dan kata sandi/)).toBeVisible();

    // No critical console errors
    const critical = errors.filter(e => !e.includes('favicon') && !e.includes('Google Analytics'));
    expect(critical).toEqual([]);
  });

  test('shows email and password inputs with icons', async ({ page }) => {
    await page.goto('/login');
    await waitForInertia(page);

    const emailInput = page.locator('input[type="email"]');
    await expect(emailInput).toBeVisible();
    await expect(emailInput).toHaveAttribute('placeholder', /kasir|email/i);

    const passwordInput = page.locator('input[type="password"]');
    await expect(passwordInput).toBeVisible();
    await expect(passwordInput).toHaveAttribute('placeholder', /kata sandi/i);

    const submitBtn = page.locator('button[type="submit"]');
    await expect(submitBtn).toBeVisible();
    await expect(submitBtn).toContainText('Masuk');
  });

  test('shows error box on invalid credentials', async ({ page }) => {
    await page.goto('/login');
    await waitForInertia(page);

    await page.fill('input[type="email"]', 'wrong@email.com');
    await page.fill('input[type="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');

    await expect(page.getByText(/Email atau kata sandi salah/i)).toBeVisible({ timeout: 10000 });
  });

  test('has no console errors on page load', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/login');
    await waitForInertia(page);

    const critical = errors.filter(e =>
      !e.includes('favicon') &&
      !e.includes('third-party') &&
      !e.includes('Google Analytics')
    );
    expect(critical).toEqual([]);
  });
});
