<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    include_once('../access-check/access-check.php');
    checkWritePermissions('bulk_mailing', $data['id']);
    $db->query('UPDATE bulk_mailing_receiver SET file=NULL WHERE bulk_mailing_id=:id',['id'=>$data['id']]);
    success('msg_bulk_mailing_generating');
?>