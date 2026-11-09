import { test, expect, Page, BrowserContext } from '@playwright/test';

let context: BrowserContext;
let sharedPage: Page;

test.beforeAll(async ({ browser }) => {
  context = await browser.newContext();
  sharedPage = await context.newPage();

  const response = await sharedPage.goto('/wp-admin');
  expect(response?.status()).toBe(200);

  await sharedPage.locator('#user_login').fill('admin');
  await sharedPage.locator('#user_pass').fill('local-dev-password');

  await Promise.all([
    sharedPage.waitForURL('**/wp-admin/'),
    sharedPage.locator('#wp-submit').click()
  ]);
});

test.afterAll(async () => {
  await context?.close();
});

test('Access admin menu', async () => {
  const page = await context.newPage();
  await page.goto('http://localhost:8080/wp-admin');

  await page.getByRole('link', {name: "Persons", exact: true}).click();
  await expect(page).toHaveURL(/post_type=person/);
  await expect(page.getByRole('heading', {name: 'Persons'})).toBeVisible();

  await page.close();
});
