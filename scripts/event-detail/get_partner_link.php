<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  
    $event = $db->getById('event',$data['event_id']);
    $eventName = $event['name'];
    $token = numberToString($user_id);
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $language = $GLOBALS['language'];
    $link = "$protocol://$host/$language/invitation/$eventName/$token";

    echo json_encode(['partner_link'=>$link]);
?>