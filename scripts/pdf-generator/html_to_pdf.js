const puppeteer = require('puppeteer');
const { exec } = require('child_process');
const util = require('util');
const execPromise = util.promisify(exec);

(async () => {
  try {
    const browser = await puppeteer.launch({
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--disable-audio-output',
        '--disable-software-rasterizer'
      ]
    });
    const page = await browser.newPage();

    // Go to URL that should be converted to PDF
    await page.goto(process.argv[2], { waitUntil: 'networkidle0' });

    // Get the number of pages
    const pageCount = await page.evaluate(() => {
      return new Promise((resolve) => {
        const pages = document.querySelectorAll('.page'); // Replace '.page' with the actual CSS class that identifies each page
        resolve(pages.length);
      });
    });

    // Create an empty PDF with multiple pages as required
    const pdf = await page.pdf({
      path: process.argv[3],
      format: 'A4',
      printBackground: true,
      landscape: false
    });

    for (let i = 2; i <= pageCount; i++) {
      await page.goto(`${process.argv[2]}?page=${i}`, { waitUntil: 'networkidle0' });

      // Add each additional page to the PDF
 /*     await page.addStyleTag({
        content: '@page { margin: 0; }'
      });*/

      await page.pdf({
        path: process.argv[3],
        format: 'A4',
        printBackground: true,
        pageRanges: `${i}`
      });
    }

    await browser.close();

    // Change owner of the created PDF file
    const userName = process.argv[4] || 'web670:web'; // Use fourth parameter or default to web670
    await execPromise(`chown ${userName} "${process.argv[3]}"`);

    console.log('PDF created with multiple pages and ownership changed successfully');
  } catch (error) {
    console.error('Error:', error);
    process.exit(1);
  }
})();