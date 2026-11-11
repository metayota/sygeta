<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    include_once('../access-check/access-check.php');
    checkReadPermissions('bulk_mailing', $data['receiver']['bulk_mailing_id']);

    $db->update('bulk_mailing_receiver',['sent'=>$data['sent']], ['id'=>$data['receiver']['bulk_mailing_id']]);
    success('msg_marked_as_sent');
?>