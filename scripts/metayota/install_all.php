<?php
namespace Metayota;
include_once('../metayota/library.php');

$resources = [];
if (getConfig('main_server')) {
    $resources = $serverDB->getAll('resource');
} else {
    $resources = $db->getAll('resource');
}

foreach($resources as $resource) {
    if (!empty($resource['implementation'])) {
        $impl = json_decode($resource['implementation'],true);
        if (!empty($impl['install.php'])) {
            callAction($resource['name'],'install');
        }
    }
}
?>