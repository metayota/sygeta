<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../access-check/access-check.php');   
    include_once('../access-check/access-check.php');

    $id = $data['id'];
    checkWritePermissions('sales_document', $id);
    $customFormData = json_encode($data['custom_form_data']);
    $db->update('sales_document', ['custom_form_data'=>$customFormData], $id);
    success('msg_saved');
?>