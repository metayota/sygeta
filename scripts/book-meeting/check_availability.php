<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');   
    $possible = true; 
    echo json_encode(['possible'=>$possible,'message'=>translate('meeting_not_possible')]);
?>