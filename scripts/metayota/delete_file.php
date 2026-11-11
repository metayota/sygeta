<?php
namespace Metayota;
include_once('../metayota/library.php');

checkApiKey();

if (getConfig('write_script_disabled', false)) {
    die('The "Write Script"-Call has been disabled for security reasons!');
}

checkApiKey();

$resourcename = $data['resourcename'];
$specificFile = $data['file'];

// If specific file is provided, write it directly without database lookup
if ($specificFile !== null && $resourcename !== null) {
    if (getConfig('wordpress')) {
        $folder = '../../scripts/'.$resourcename.'/';
    } else {
        $folder = '../'.$resourcename.'/';
    }
    
    if (!is_dir($folder)) {
        mkdir($folder);
    }
    
    // Determine file name using same logic as before
    $fileName = $folder.$specificFile;
    if (file_exists($fileName)) {
        echo "Deleted File $fileName";
        unlink($fileName);
    } else {
        echo "File not exist";
    }
}
   


echo json_encode(['success'=>true]);
?>