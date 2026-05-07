import { Page, expect } from '@playwright/test';

/**
 * Navigate and wait for the Inertia page to fully render.
 * Inertia SPA navigations trigger a DOM update without full page reload.
 * Use this helper when clicking Inertia <Link> components.
 */
export async function waitForInertia(page: Page) {
  // Inertia sets a data-page attribute on the #app div
  await page.waitForSelector('#app[data-page]', { timeout: 10000 });
}

/**
 * Assert the current Inertia page component name matches expected.
 * The component name is embedded in #app's data-page attribute.
 */
export async function expectPageComponent(page: Page, componentName: string) {
  const dataPage = await page.getAttribute('#app', 'data-page');
  if (!dataPage) throw new Error('No Inertia page found');
  const parsed = JSON.parse(dataPage);
  expect(parsed.component).toBe(componentName);
}

/**
 * Visit an Inertia page via URL and wait for render.
 */
export async function visit(page: Page, url: string) {
  await page.goto(url);
  await waitForInertia(page);
}

/**
 * Assert the URL matches a regex pattern (e.g. after redirect).
 */
export async function expectUrl(page: Page, pattern: RegExp) {
  await expect(page).toHaveURL(pattern);
}

/**
 * Scrollable customer mobile page helper — wait for content to settle.
 */
export async function waitForMobilePage(page: Page) {
  await page.waitForTimeout(500);
  await waitForInertia(page);
}
