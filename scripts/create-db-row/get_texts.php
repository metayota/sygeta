<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    echo json_encode(['title'=>translate('create_row_title_'.$data['table'])]);
?>