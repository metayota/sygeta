<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    if (empty($data['step'])) {
        $newData[$data['field']] = 1;
        $db->update('customer',$newData,['id'=>$data['customer_id']]);
        success();
    }
?>