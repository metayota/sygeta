<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

$filename = basename($_FILES['file']['name']);

$pattern = '/^[a-zA-Z_-]+$/';

$allowedFolders = getConfig('upload_folders');

if ($allowedFolders !== NULL) {
    if (!in_array($data['folder'], $allowedFolders)) {
        die('Please set the config upload_folders to include the desired folders');
    }
}

if (!preg_match($pattern, $data['folder'])) {
    die('Folder is invalid!');
}

$folder = !empty($data['folder']) && !str_contains($data['folder'],'..') ? $data['folder'].'/' : '';
$filenameParts = explode('.', $filename);
$extension = strtolower(end($filenameParts));
$allowed = getConfig('form_file_allowed_types', array(
    // Bilder
    'pdf','jpg','jpeg','gif','png','webp','svg','avif',
    // Videos
    'mov','webm','mp4','mpg','mpeg',
    // PowerPoint
    'ppt','pptx','ppsx','pps',
    // Word
    'doc','docx','rtf',
    // Excel (nützlich für Finanzpläne)
    'xls','xlsx',
    // OpenOffice/LibreOffice
    'odt','odp','ods',
    // Weitere nützliche Formate
    'txt','csv'
));
if (!in_array($extension, $allowed)) {
    $msg = json_encode(["message"=>translate('filetype_not_allowed'),'iframe'=>$_POST['iframe']]);
    echo ' <html><head><script type="text/javascript">window.parent.postMessage('.$msg.', "*")</script></head><body>success</body></html>';
    die();
}

if (getConfig('wordpress')) {
    // For WordPress environment
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/' . $folder;
    $target_url = $upload_dir['baseurl'] . '/' . $folder;
    $home_url = home_url();
    $target_url = str_replace($home_url, '', $target_url);
} else {
    // For non-WordPress environment
    $target_dir = '../../uploads/' . $folder;
}

if (!empty($folder)) {
    if (!is_dir($target_dir)) {
        mkdir($target_dir);
    }
}

$filename = str_replace(' ', '_', $filename);
$filename = preg_replace('/[^a-zA-Z0-9\.\_\-]/', '_', $filename);
while (file_exists($target_dir . $filename)) {
    $filename = rand(0,9) . $filename;
}

$moved = move_uploaded_file($_FILES['file']['tmp_name'], $target_dir . $filename);

if ($moved) {
    if (getConfig('wordpress')) {
        $filename = $target_url.$filename;
    }
    echo ' <html><head><script type="text/javascript">window.parent.postMessage({filename:\'' . $filename . '\', iframe:\'' . $_POST['iframe'] . '\'}, "*")</script></head><body>success</body></html>';
} else {
    echo "Not uploaded";
}

?>