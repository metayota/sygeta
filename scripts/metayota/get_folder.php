<?php
namespace Metayota;
include_once('../metayota/library.php');

checkApiKey();

$resourcename = !empty($get['resourcename']) ? $get['resourcename'] : $data['resourcename'];

if (empty($resourcename)) {
    echo json_encode(['success' => false, 'error' => 'No resourcename provided']);
    exit;
}

$files = getResourceFiles($resourcename);

echo json_encode(['success' => true, 'files' => $files], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
