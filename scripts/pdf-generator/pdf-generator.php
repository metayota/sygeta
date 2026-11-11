<?php namespace Metayota; include_once('../metayota/library.php');  
include_once('../pdf-generator/lib.php');

require_once '../../vendor/autoload.php';
use setasign\Fpdi\Fpdi;

// Error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Default paths and configuration
if (getConfig('wordpress')) {
    $applicationPath = dirname(__DIR__, 6);
} else {
    $applicationPath = dirname(__DIR__, 3);
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$serverURL = $protocol . $_SERVER['HTTP_HOST'];

// Get current user and group
try {
    $user = posix_getpwuid(posix_geteuid())['name'];
    $group = posix_getgrgid(posix_getegid())['name'];
} catch (\Exception $e) {
    error_log("Error getting user/group info: " . $e->getMessage());
    die(json_encode(['error' => 'Failed to get user/group information']));
}

// Validate input data
if (!isset($data['save_path']) && !isset($data['page'])) {
    die(json_encode(['error' => 'Missing required parameters']));
}

// Ensure save_path has no duplicate .pdf
$extension = pathinfo($data['save_path'], PATHINFO_EXTENSION) === 'pdf' ? '' : '.pdf';
$uniqueId = uniqid();
$savePath = $data['save_path'] ? $applicationPath.'/html'.$data['save_path'] : $applicationPath.'/html/uploads/temp_'.$uniqueId.'.pdf';

// Decode HTML entities to ensure proper URL formatting
$page = $data['page'] ? html_entity_decode($serverURL.$data['page']) : '';

$footerParams = $data['template_params'] ?? [];
$footerUrl = $data['print_template'] ?? 'print-template-bill';

// Ensure save directory exists
$saveDir = dirname($savePath);
if (!is_dir($saveDir)) {
    if (!mkdir($saveDir, 0755, true)) {
        die(json_encode(['error' => 'Failed to create save directory']));
    }
}

// Remove existing file if it exists
if (file_exists($savePath)) {
    if (!unlink($savePath)) {
        die(json_encode(['error' => 'Failed to remove existing file']));
    }
}

// Create the base PDF
$escapedPage = escapeshellarg($page);  // Proper URL escaping
$scriptsFolder = 'scripts';
if (getConfig('wordpress')) {
    $scriptsFolder = 'wp-content/plugins/metayota/scripts';
}

// Command generation
$command = 'sudo node '.$applicationPath.'/html/'.$scriptsFolder.'/pdf-generator/html_to_pdf.js '.$escapedPage.' "'.$savePath.'.temp.pdf" "'.$user.':'.$group.'" 2>&1';

// Execute the command
$output = shell_exec($command);

error_log("PDF Command Output: " . $output);
sleep(1);

// Directly check if the PDF was created
if (!file_exists($savePath.'.temp.pdf')) {
    error_log("PDF not found: $savePath");
    die(json_encode(['error' => 'PDF was not found: ' . $savePath,'command'=>$command]));
}

try {
    // Create temporary instance to get page count
    $tempPdf = new Fpdi();
    $pageCount = $tempPdf->setSourceFile($savePath.'.temp.pdf');

   /* if ($pageCount < 2) {
        error_log("Detected less than 2 pages in the source PDF.");
        die(json_encode(['error' => 'Only one page found in the PDF']));
    }*/

    // Debug: Log the actual page count for verification
    error_log("Detected page count: $pageCount");

} catch (\Exception $e) {
    error_log("FPDI Error: " . $e->getMessage());
    die(json_encode(['error' => 'Failed to process PDF file']));
}

// Setup cache directory
$cacheDir = "../../cache/";
if (!is_dir($cacheDir)) {
    if (!mkdir($cacheDir, 0755, true)) {
        die(json_encode(['error' => 'Failed to create cache directory']));
    }
}

// Build footer parameters string
$footerParamsString = '';
if (!empty($footerParams)) {
    foreach ($footerParams as $key => $value) {
        if (!is_array($value)) {
            $footerParamsString .= '&' . urlencode($key) . '=' . urlencode($value);
        }
    }
}

// Generate or retrieve cached header/footer PDFs
$headerFooterPages = [];
for ($i = 1; $i <= $pageCount; $i++) {
    $cacheStr = json_encode($db->get('company_settings')).json_encode($db->get('company_payment_method'));
    $cacheKey = md5($footerUrl . $footerParamsString . '&page=' . $i . '&count_pages=' . $pageCount.$cacheStr);
    $headerFooterPath = $cacheDir . "header_footer_" . $cacheKey . ".pdf";

    if (!file_exists($headerFooterPath)) {
        $lang = !empty($data['language']) ? $data['language'] : $GLOBALS['language'];
        $headerFooterCommand = 'sudo node '.$applicationPath.'/html/'.$scriptsFolder.'/pdf-generator/html_to_pdf.js "'.$serverURL.'/'.$lang.'/call/' . 
            $footerUrl . '?page=' . $i . '&count_pages=' . $pageCount . $footerParamsString . '" "' . $headerFooterPath . '"';
        shell_exec($headerFooterCommand);
    }

    if (file_exists($headerFooterPath)) {
        $headerFooterPages[$i] = $headerFooterPath;
    }
}

try {
    $mainPdf = new Fpdi();
    $fixedWidth = 210;
    $fixedHeight = 297;
    $orientation = 'P';

    $mainPdf->SetAutoPageBreak(false);
    $mainPdf->SetCompression(true);

    for ($i = 1; $i <= $pageCount; $i++) {
        error_log("Processing page $i of $pageCount");
        $mainPdf->AddPage($orientation, [$fixedWidth, $fixedHeight]);
        
        // Import page from the original PDF
        $mainPdf->setSourceFile($savePath.'.temp.pdf');
        $mainTplId = $mainPdf->importPage($i);
        $mainPdf->useTemplate($mainTplId, 0, 0, $fixedWidth, $fixedHeight);
        
        // Import and add header/footer if available
        if (isset($headerFooterPages[$i]) && file_exists($headerFooterPages[$i])) {
            $mainPdf->setSourceFile($headerFooterPages[$i]);
            $headerFooterTplId = $mainPdf->importPage(1);
            $mainPdf->useTemplate($headerFooterTplId, 0, 0, $fixedWidth, $fixedHeight);
        }
    }

    $finalSavePath = $savePath;
    if (file_exists($finalSavePath)) {
        unlink($finalSavePath);
    }

    // *** MODIFIED: Check if ZUGFeRD is needed ***
    if (isset($data['template_params']['zugferd_data'])) {
        // Generate PDF content in memory first
        $pdfContent = $mainPdf->Output('S'); // 'S' = string output
        
        // Create ZUGFeRD-compliant PDF
        $zugferdPdf = createZugferdPdf($pdfContent, $data['template_params']['zugferd_data']);
        
        // Save ZUGFeRD PDF
        file_put_contents($finalSavePath, $zugferdPdf);
    } else {
        // Standard PDF output
        $mainPdf->Output($finalSavePath, 'F');
    }

    echo json_encode(['pdf_path' => '/cache/' . basename($finalSavePath)]);

} catch (\Exception $e) {
    error_log("PDF Processing Error: " . $e->getMessage());
    die(json_encode(['error' => 'Failed to process and save final PDF' . $e->getMessage()]));
}
?>