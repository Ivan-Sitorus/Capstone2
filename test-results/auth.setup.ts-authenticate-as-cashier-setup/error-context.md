# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: auth.setup.ts >> authenticate as cashier
- Location: tests/playwright/auth.setup.ts:50:3

# Error details

```
TimeoutError: page.waitForSelector: Timeout 15000ms exceeded.
Call log:
  - waiting for locator('input[type="email"], input[name="email"]') to be visible

```

# Page snapshot

```yaml
- main [ref=e2]:
  - generic [ref=e4]:
    - heading "404" [level=1] [ref=e5]
    - generic [ref=e6]: Not Found
```

# Test source

```ts
  1  | import { test as setup, expect } from '@playwright/test';
  2  | import path from 'path';
  3  | 
  4  | const AUTH_DIR = path.resolve('tests/playwright/auth');
  5  | 
  6  | const CREDENTIALS = {
  7  |   admin: {
  8  |     email: 'admin@w9cafe.com',
  9  |     password: 'password',
  10 |     storageState: path.join(AUTH_DIR, 'admin.json'),
  11 |     expectedUrl: /\/cashier\/pesanan-baru/,
  12 |   },
  13 |   cashier: {
  14 |     email: 'kasir@w9cafe.com',
  15 |     password: 'password',
  16 |     storageState: path.join(AUTH_DIR, 'cashier.json'),
  17 |     expectedUrl: /\/cashier\/pesanan-baru/,
  18 |   },
  19 |   customer: {
  20 |     storageState: path.join(AUTH_DIR, 'customer.json'),
  21 |   },
  22 | };
  23 | 
  24 | async function doLogin({ page, email, password, expectedUrl }) {
  25 |   await page.goto('/login');
> 26 |   await page.waitForSelector('input[type="email"], input[name="email"]', { timeout: 15000 });
     |              ^ TimeoutError: page.waitForSelector: Timeout 15000ms exceeded.
  27 |   await page.fill('input[type="email"], input[name="email"]', email);
  28 |   await page.fill('input[type="password"], input[name="password"]', password);
  29 |   await page.click('button[type="submit"]');
  30 |   // Inertia SPA may navigate before waitForURL catches it — use toHaveURL instead
  31 |   await expect(page).toHaveURL(expectedUrl, { timeout: 15000 });
  32 | }
  33 | 
  34 | async function doCustomerSetup({ page, storageState }) {
  35 |   // Customer identitas form is embedded in /customer/menu
  36 |   await page.goto('/customer/menu?table=1');
  37 |   
  38 |   // Fill name and phone on the identitas form
  39 |   await page.waitForSelector('input[type="text"]', { timeout: 15000 });
  40 |   await page.fill('input[type="text"]', 'Budi Test');
  41 |   await page.fill('input[type="tel"]', '081234567890');
  42 |   
  43 |   // Click the Masuk button
  44 |   await page.click('button:has-text("Masuk")');
  45 |   await expect(page).toHaveURL(/\/customer\/menu/, { timeout: 15000 });
  46 |   await page.context().storageState({ path: storageState });
  47 | }
  48 | 
  49 | for (const [role, creds] of Object.entries(CREDENTIALS)) {
  50 |   setup(`authenticate as ${role}`, async ({ page }) => {
  51 |     if (role === 'customer') {
  52 |       await doCustomerSetup({ page, storageState: creds.storageState });
  53 |     } else {
  54 |       await doLogin({ page, ...creds });
  55 |       await page.context().storageState({ path: creds.storageState });
  56 |     }
  57 |   });
  58 | }
  59 | 
```