<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../php-mailer/php-mailer.php');  
    $event = $db->getById('event',$data['event_id']);
    $event['title_translated'] = translate($event['title']);
    echo json_encode($event);
?>