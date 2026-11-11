<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');   
    echo json_encode($db->getAllByQuery('SELECT *,address as name, id as value FROM property'));
?>