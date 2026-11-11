<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../sync-functions/sync-functions.php');   include_once('../translation-service/translation-service.php');   
    if (!empty($serverDB)) {
        $serverDB->update('resource', ['visibility'=>'private'], ['name'=>$data['resource_name']]);
        success();
    }
?>