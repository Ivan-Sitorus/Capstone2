import { test as setup, expect } from '@playwright/test';
import path from 'path';

const AUTH_DIR = path.resolve('tests/playwright/auth');

const CREDENTIALS = {
  admin: {
    email: 'admin@w9cafe.com',
    password: 'password',
    storageState: path.join(AUTH_DIR, 'admin.json'),
    expectedUrl: /\/cashier\/dashboard/,
  },
  cashier: {
    email: 'kasir@w9cafe.com',
    password: 'password',
    storageState: path.join(AUTH_DIR, 'cashier.json'),
    expectedUrl: /\/cashier\/dashboard/,
  },
  customer: {
    email: 'budi@student.com',
    password: 'password',
    storageState: path.join(AUTH_DIR, 'customer.json'),
    expectedUrl: /\/customer\/menu/,
    loginUrl: '/customer/login',
    name: 'Budi Santoso',
    nim: '21120122140001',
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

async function doCustomerLogin({ page, name, nim, expectedUrl }) {
  await page.goto('/customer/login');
  await page.waitForSelector('input[name="name"], input[placeholder*="nama"], input[type="text"]', { timeout: 15000 });
  await page.fill('input[name="name"], input[placeholder*="nama"], input[type="text"]', name);
  await page.fill('input[type="password"]', nim);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(expectedUrl, { timeout: 15000 });
}

for (const [role, creds] of Object.entries(CREDENTIALS)) {
  setup(`authenticate as ${role}`, async ({ page }) => {
    if (role === 'customer') {
      await doCustomerLogin({ page, ...creds });
    } else {
      await doLogin({ page, ...creds });
    }
    await page.context().storageState({ path: creds.storageState });
  });
}
