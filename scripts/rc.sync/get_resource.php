<?php 
namespace Metayota; 
include_once('../metayota/library.php');   
include_once('../sync-functions/sync-functions.php');   
include_once('../translation-service/translation-service.php');
include_once('../metayota/resource-functions.php');

$apiKey = getConfig('metayota_sync_api_key');
if (empty($apiKey) || $apiKey != $_GET['api_key']) {
    errorMsg(translate('api_key_not_correct'));
}

$resource = $db->get('resource', array('name'=>$_GET['resourcename'], 'visibility'=>'published'));

if (!empty($resource)) {
    // Load implementation from files instead of database
    $files = getResourceFiles($resource['name']);
    $resource['implementation'] = json_encode($files);
}

echo json_encode($resource);
?>