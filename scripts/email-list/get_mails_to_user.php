<?php namespace Metayota; include_once('../metayota/library.php');   
    $mailMessages = $db->getAll('mail_message',['contact_id'=>$data['user_id']]);
    echo json_encode($mailMessages);
?>