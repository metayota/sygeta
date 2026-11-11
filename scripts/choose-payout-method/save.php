<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $partner = $db->get('partner', ['user_id'=>$user_id]);
    $data['partner_id'] = $partner['id'];
     $data['payout_data'] = json_encode($data['payout_data']);
    $db->insertOrUpdate('partner_payout_method', $data, 'partner_id');
    success('msg_saved');
?>