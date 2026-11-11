<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../sync-functions/sync-functions.php');   include_once('../translation-service/translation-service.php');  
   // requireAccess('database','repository');
    $repositories = $serverDB->getAll('repository');
    echo(json_encode($repositories));
?>