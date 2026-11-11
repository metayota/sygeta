const puppeteer = require('puppeteer');
const { exec } = require('child_process');
const util = require('util');
const execPromise = util.promisify(exec);

(async () => {
  try {
    const browser = await puppeteer.launch({
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const page = await browser.newPage();

    // Go to URL that should be converted to PDF
    await page.goto(process.argv[2], { waitUntil: 'networkidle0' });

    // Create PDF
    await page.pdf({
      path: process.argv[3], // Path where PDF will be saved
      format: 'A4',
      printBackground: true
    });

    await browser.close();

    // Change owner of the created PDF file
    const userName = process.argv[4] || 'web670:web'; // Use fourth parameter or default to web670
    await execPromise(`chown ${userName} "${process.argv[3]}"`);
    
    console.log('PDF created and ownership changed successfully');
  } catch (error) {
    console.error('Error:', error);
    process.exit(1);
  }
})();
