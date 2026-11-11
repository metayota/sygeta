<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $partner = $db->get('partner',['user_id'=>$user_id]);
    if ($partner) {
        echo json_encode(['partner_id'=>$partner['id']]);
    } else {
        success();
    }
?>