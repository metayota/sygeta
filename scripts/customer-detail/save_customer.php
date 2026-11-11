<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    include_once('../access-check/access-check.php');
    checkWritePermissions('customer', $data['id']);

    $db->update('customer',$data,['id'=>$data['id']]);
    msg('msg_customer_saved',[],'success');
?>