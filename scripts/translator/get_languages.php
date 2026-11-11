<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    if (getConfig('main_server')) {
        $db = $serverDB;
    }

    $languages = $db->getAll('language');
    echo json_encode($languages);
?>