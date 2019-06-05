

// describe('I connect to BO', () => {
//   beforeAll(async () => {
//     await page.goto('https://checkout.qa-prestashopready.net/backoffice');
    
//     const loginInput = await page.$('input#email');
//     await loginInput.type('aurelien.pelletier+checkout@prestashop.com');
//     const pwdInput = await page.$('input#passwd');
//     await pwdInput.type('azerty01');
//     await page.click('button#submit_login');
//     await page
//     .waitForNavigation({
//       timeout: 30000,
//     });
//   });

//   it('should display "Bienvenue dans votre boutique !" text on page', async () => {
//     await expect(page).toMatch("Bienvenue dans votre boutique !");
//   })

// });
const puppeteer = require("puppeteer");

let page;
let browser;
const webpage = "https://checkout.qa-prestashopready.net/backoffice";
const width = 1440;
const height = 900;
const myBlog = "blog.png"
const email=process.env.BO_LOGIN || 'aurelien.pelletier+checkout@prestashop.com'
const password=process.env.BO_PASSWORD || 'azerty01'
jest.setTimeout(30000)

describe("I connect to BO", () => {

  beforeAll(async () => {
    browser = await puppeteer.launch({
      headless: true,
      args: ["--no-sandbox", "--disable-setuid-sandbox"]
    });
    page = await browser.newPage();
    await page.setViewport({ width, height });
  });

  afterAll(async () => {
    await page.screenshot({path: myBlog});
    browser.close();
  });

  it('should display "Bienvenue dans votre boutique !" text on page', async () => {
    await page.goto(webpage);
    const loginInput = await page.$('input#email');
    await loginInput.type('aurelien.pelletier+checkout@prestashop.com');
    const pwdInput = await page.$('input#passwd');
    await pwdInput.type('azerty01');
    await page.click('button#submit_login');
    await page
    .waitForNavigation({
      timeout: 30000,
    });
    await expect(page).toMatch("Bienvenue dans votre boutique !");
  })
});