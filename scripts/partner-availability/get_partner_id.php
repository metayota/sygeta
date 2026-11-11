<?php namespace Metayota; include_once('../metayota/library.php');   
    $partner = $db->get('partner',['user_id'=>$user_id]);
    echo json_encode(['partner_id'=>$partner['id']]);
?>