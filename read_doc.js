const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.goto('https://docs.faspay.co.id/merchant-integration/api-reference-1/snap/signature-snap', {waitUntil: 'networkidle2'});
    const text = await page.evaluate(() => document.body.innerText);
    console.log(text);
    await browser.close();
})();
