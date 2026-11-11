<?php namespace Metayota; include_once('../metayota/library.php');  
// Navigate two directories up and install FPDF and FPDI
echo "Current Directory: " . getcwd();

$output = shell_exec('cd ../.. && composer require setasign/fpdf:^1.8 setasign/fpdi:^2.6 2>&1');
$output.= "\n\nPerforming npm install:\n";
$output.= shell_exec('cd ../../scripts/pdf-generator/ && npm install');

// Display the output 3
echo "<pre>$output</pre>";

shell_exec('cd ../scripts/pdf-generator')
shell_exec('npx puppeteer browsers install chrome')

?>