import { test as setup, expect } from '@playwright/test';
import path from 'path';

const AUTH_DIR = path.resolve('tests/playwright/auth');

const CREDENTIALS = {
  admin: {
    email: 'admin@w9cafe.com',
    password: 'password',
    storageState: path.join(AUTH_DIR, 'admin.json'),
    expectedUrl: /\/cashier\/pesanan-baru/,
  },
  cashier: {
    email: 'kasir@w9cafe.com',
    password: 'password',
    storageState: path.join(AUTH_DIR, 'cashier.json'),
    expectedUrl: /\/cashier\/pesanan-baru/,
  },
  customer: {
    storageState: path.join(AUTH_DIR, 'customer.json'),
  },
};

async function doLogin({ page, email, password, expectedUrl }) {
  await page.goto('/login');
  await page.waitForSelector('input[type="email"], input[name="email"]', { timeout: 15000 });
  await page.fill('input[type="email"], input[name="email"]', email);
  await page.fill('input[type="password"], input[name="password"]', password);
  await page.click('button[type="submit"]');
  // Inertia SPA may navigate before waitForURL catches it — use toHaveURL instead
  await expect(page).toHaveURL(expectedUrl, { timeout: 15000 });
}

async function doCustomerSetup({ page, storageState }) {
  // Customer identitas form is embedded in /customer/menu
  await page.goto('/customer/menu?table=1');
  
  // Fill name and phone on the identitas form
  await page.waitForSelector('input[type="text"]', { timeout: 15000 });
  await page.fill('input[type="text"]', 'Budi Test');
  await page.fill('input[type="tel"]', '081234567890');
  
  // Click the Masuk button
  await page.click('button:has-text("Masuk")');
  await expect(page).toHaveURL(/\/customer\/menu/, { timeout: 15000 });
  await page.context().storageState({ path: storageState });
}

for (const [role, creds] of Object.entries(CREDENTIALS)) {
  setup(`authenticate as ${role}`, async ({ page }) => {
    if (role === 'customer') {
      await doCustomerSetup({ page, storageState: creds.storageState });
    } else {
      await doLogin({ page, ...creds });
      await page.context().storageState({ path: creds.storageState });
    }
  });
}
