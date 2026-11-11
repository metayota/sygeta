<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    include_once('../access-check/access-check.php');
    checkReadPermissions('bulk_mailing', $data['id']);
    
$cachePath = "../../cache/"; // Ensure this directory exists and is writable
$mailingID = $data['id'];
$secureFileName = "bulk_mailing_" . $mailingID . "_" . bin2hex(random_bytes(8)) . ".zip";
$zipFilePath = $cachePath . $secureFileName;


// Check if there are still documents being generated
$stillGenerating = $db->getByQuery("SELECT * FROM bulk_mailing_receiver WHERE bulk_mailing_id=:mailing_id AND file IS NULL", ['mailing_id' => $mailingID]);

if ($stillGenerating) {
    echo json_encode(['error' => translate('msg_documents_generating_pls_wait')]);
    exit;
}

// Fetch all available file paths from the database
$files = $db->getAllByQuery("SELECT file FROM bulk_mailing_receiver WHERE bulk_mailing_id=:mailing_id AND file IS NOT NULL", ['mailing_id' => $mailingID]);

if (!$files) {
    echo json_encode(['error' => translate('msg_no_files_found')]);
    exit;
}

$zip = new \ZipArchive();
if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== TRUE) {
    echo json_encode(['error' => translate('msg_zip_creation_failed')]);
    exit;
}

// Add files to the ZIP archive
foreach ($files as $file) {
    $filePath = "../../" . $file['file']; // Adjusting relative path
    if (file_exists($filePath)) {
        $zip->addFile($filePath, basename($filePath));
    }
}

$zip->close();

// Provide JSON response with the secure ZIP filename
if (file_exists($zipFilePath)) {
    echo json_encode(['zip' => "/cache/" . $secureFileName]); // Return a safe path for the frontend
} else {
    echo json_encode(['error' => translate('msg_zip_creation_failed')]);
}
?>