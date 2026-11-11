<?php namespace Metayota; include_once('../metayota/library.php');   
    include_once('../access-check/access-check.php');
    checkReadPermissions('contact', $data['contact_id']);

    $mailMessages = $db->getAll('mail_message',['receiver_contact_id'=>$data['contact_id']]);
    echo json_encode($mailMessages);
?>