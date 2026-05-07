# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: customer/login.spec.ts >> C4 - Identitas Pelanggan (Customer Login) >> shows Scan QR Meja page when no table parameter
- Location: tests/playwright/customer/login.spec.ts:9:3

# Error details

```
Error: expect(received).toEqual(expected) // deep equality

- Expected  - 1
+ Received  + 3

- Array []
+ Array [
+   "Loading the stylesheet 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap' violates the following Content Security Policy directive: \"style-src 'self' 'unsafe-inline' http://127.0.0.1:5173 http://localhost:5173\". Note that 'style-src-elem' was not explicitly set, so 'style-src' is used as a fallback. The action has been blocked.",
+ ]
```

# Page snapshot

```yaml
- generic [ref=e4]:
  - img "W9 Cafe" [ref=e6]
  - heading "Scan QR Meja" [level=1] [ref=e7]
  - paragraph [ref=e8]: Silakan scan QR code yang ada di meja Anda untuk mulai memesan.
  - paragraph [ref=e9]: Hubungi kasir jika membutuhkan bantuan.
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { waitForMobilePage } from '../helpers';
  3  | 
  4  | test.describe('C4 - Identitas Pelanggan (Customer Login)', () => {
  5  |   test.beforeEach(({ page }, testInfo) => {
  6  |     if (testInfo.project.name !== 'customer') test.skip();
  7  |   });
  8  | 
  9  |   test('shows Scan QR Meja page when no table parameter', async ({ page }) => {
  10 |     const errors: string[] = [];
  11 |     page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
  12 | 
  13 |     await page.goto('/order');
  14 |     await waitForMobilePage(page);
  15 | 
  16 |     await expect(page.getByText('Scan QR Meja')).toBeVisible({ timeout: 10000 });
  17 |     await expect(page.getByText(/scan QR code yang ada di meja/i)).toBeVisible({ timeout: 5000 });
  18 | 
  19 |     const critical = errors.filter(e => !e.includes('favicon'));
> 20 |     expect(critical).toEqual([]);
     |                      ^ Error: expect(received).toEqual(expected) // deep equality
  21 |   });
  22 | 
  23 |   test('renders W9 Cafe logo and Selamat Datang with table param', async ({ page }) => {
  24 |     await page.goto('/order?table=1');
  25 |     await waitForMobilePage(page);
  26 | 
  27 |     await expect(page.getByText('Selamat Datang!')).toBeVisible({ timeout: 10000 });
  28 |     await expect(page.getByText(/Meja No./)).toBeVisible({ timeout: 5000 });
  29 |   });
  30 | 
  31 |   test('displays Nama and Nomor Telepon input fields', async ({ page }) => {
  32 |     await page.goto('/order?table=1');
  33 |     await waitForMobilePage(page);
  34 | 
  35 |     await expect(page.getByText('Nama')).toBeVisible({ timeout: 5000 });
  36 |     await expect(page.getByText('Nomor Telepon')).toBeVisible({ timeout: 5000 });
  37 | 
  38 |     const nameInput = page.locator('input[type="text"]');
  39 |     await expect(nameInput).toBeVisible();
  40 | 
  41 |     const phoneInput = page.locator('input[type="tel"]');
  42 |     await expect(phoneInput).toBeVisible();
  43 |   });
  44 | 
  45 |   test('displays mahasiswa checkbox option', async ({ page }) => {
  46 |     await page.goto('/order?table=1');
  47 |     await waitForMobilePage(page);
  48 | 
  49 |     await expect(page.getByText(/mahasiswa STIE Totalwin Semarang/)).toBeVisible({ timeout: 5000 });
  50 |   });
  51 | 
  52 |   test('displays Masuk submit button', async ({ page }) => {
  53 |     await page.goto('/order?table=1');
  54 |     await waitForMobilePage(page);
  55 | 
  56 |     const masukBtn = page.getByRole('button', { name: 'Masuk' });
  57 |     await expect(masukBtn).toBeVisible({ timeout: 5000 });
  58 |   });
  59 | 
  60 |   test('validates empty form shows error messages', async ({ page }) => {
  61 |     await page.goto('/order?table=1');
  62 |     await waitForMobilePage(page);
  63 | 
  64 |     const masukBtn = page.getByRole('button', { name: 'Masuk' });
  65 |     await masukBtn.click();
  66 |     await page.waitForTimeout(500);
  67 | 
  68 |     const nameError = page.getByText(/Nama minimal 2 karakter/);
  69 |     const phoneError = page.getByText(/Nomor telepon tidak valid/);
  70 |     const hasError = await nameError.isVisible().catch(() => false) || await phoneError.isVisible().catch(() => false);
  71 |     expect(hasError).toBeTruthy();
  72 |   });
  73 | 
  74 |   test('successful identity entry redirects to menu', async ({ page }) => {
  75 |     await page.goto('/order?table=1');
  76 |     await waitForMobilePage(page);
  77 | 
  78 |     await page.fill('input[type="text"]', 'Test Customer');
  79 |     await page.fill('input[type="tel"]', '081234567890');
  80 | 
  81 |     const masukBtn = page.getByRole('button', { name: 'Masuk' });
  82 |     await masukBtn.click();
  83 | 
  84 |     await page.waitForURL(/\/customer\/menu/, { timeout: 10000 });
  85 |     await expect(page).toHaveURL(/\/customer\/menu/);
  86 |   });
  87 | });
  88 | 
```