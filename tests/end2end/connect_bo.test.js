jest.setTimeout(30000);
describe('Google', () => {
  beforeAll(async () => {
    await page.goto('http://localhost/admin-dev');
    const loginInput = await page.$('input#email');
    await loginInput.type('demo@prestashop.com');
    const pwdInput = await page.$('input#passwd');
    await pwdInput.type('prestashop_demo');
    await page.click('button#submit_login');
    await page
    .waitForNavigation({
      timeout: 30000,
    });
  });

  it('should display "Preston" text on page', async () => {
    await expect(page).toMatch("Preston");
  })

});
