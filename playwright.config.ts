import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/playwright',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['html', { outputFolder: 'tests/playwright/report' }],
    ['list'],
  ],
  timeout: 60000,
  expect: {
    timeout: 15000,
  },
  use: {
    baseURL: 'http://localhost:8081',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'setup',
      testMatch: /auth\.setup\.ts/,
      testDir: './tests/playwright',
    },
    {
      name: 'admin',
      dependencies: ['setup'],
      testMatch: /admin\/.*\.spec\.ts/,
      use: {
        ...devices['Desktop Chrome'],
        viewport: { width: 1280, height: 800 },
        storageState: 'tests/playwright/auth/admin.json',
      },
    },
    {
      name: 'cashier',
      dependencies: ['setup'],
      testMatch: /(cashier|smoke|integration)\/.*\.spec\.ts/,
      use: {
        ...devices['Desktop Chrome'],
        viewport: { width: 1280, height: 800 },
        storageState: 'tests/playwright/auth/cashier.json',
      },
    },
    {
      name: 'customer',
      dependencies: ['setup'],
      testMatch: /customer\/.*\.spec\.ts/,
      timeout: 60000,
      use: {
        // Chrome with mobile viewport (430×932) instead of WebKit
        browserName: 'chromium',
        viewport: { width: 430, height: 932 },
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        hasTouch: true,
        isMobile: true,
        storageState: 'tests/playwright/auth/customer.json',
      },
    },
    {
      name: 'kitchen',
      testMatch: /kitchen\/.*\.spec\.ts/,
      use: {
        ...devices['Desktop Chrome'],
        viewport: { width: 1280, height: 800 },
        storageState: 'tests/playwright/auth/cashier.json',
      },
    },
  ],
});
