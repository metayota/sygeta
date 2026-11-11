<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $data['id'] = 1;
    $data['payment_method_data'] = json_encode($data['payment_method_data']);
    $db->insertOrUpdate('company_payment_method', $data, 'id');
    success('msg_saved');
?>