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
    email: 'budi@customer.com',
    password: 'password',
    storageState: path.join(AUTH_DIR, 'customer.json'),
    expectedUrl: /\/customer\/menu/,
  },
};

async function doLogin({ page, email, password, expectedUrl }) {
  await page.goto('/login');
  await page.waitForSelector('input[type="email"], input[name="email"]', { timeout: 15000 });
  await page.fill('input[type="email"], input[name="email"]', email);
  await page.fill('input[type="password"], input[name="password"]', password);
  await page.click('button[type="submit"]');
  // Inertia.location triggers a full page navigation on auth success
  await page.waitForURL(expectedUrl, { timeout: 15000 });
}

for (const [role, creds] of Object.entries(CREDENTIALS)) {
  setup(`authenticate as ${role}`, async ({ page }) => {
    await doLogin({ page, ...creds });
    await page.context().storageState({ path: creds.storageState });
  });
}
