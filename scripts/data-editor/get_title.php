<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    echo json_encode(translate($db->getColumnByName('resource', 'title', $data['table'])));
?>