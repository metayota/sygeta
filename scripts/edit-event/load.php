<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../mail-helper/mail-helper.php');  
    include_once('../access-check/access-check.php');
    checkReadPermissions('calendar_event', $data['event_id']);

    $event = $db->getById('calendar_event',$data['event_id']);
    echo json_encode($event);
?>