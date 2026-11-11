<?php
namespace Metayota;
include_once('../metayota/library.php');

sendCacheHeaders(82000);

if (!empty($get['config_wordpress'])) {
    global $metayota_config;
    $metayota_config['wordpress'] = true;
    $metayota_config['main_server'] = false;
}

$resources = $get['resources'];
//$resources = ['app','form.button'];
$allResourcesJSON = [];
foreach($resources as $resource) {
    $resourceJSON = callAction('metayota','resource',['name'=>$resource,'output'=>'json']);
    $allResourcesJSON[$resource] =json_decode($resourceJSON);
}
echo json_encode($allResourcesJSON);
?>