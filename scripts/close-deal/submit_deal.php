<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  
    if (isset($data['id'])) {
        $db->update('closed_deal', $data);
        $dealID = $data['id'];
        echo json_encode(['success'=>true,'message'=>translate('please_check_deal_confirm'),'closed_deal_id'=>$dealID]);
    } else {
        $db->insert('closed_deal',$data);
        $dealID = $db->lastInsertId();
        echo json_encode(['success'=>true,'message'=>translate('please_check_deal_confirm'),'inserted' => true, 'closed_deal_id'=>$dealID]);
    }
?>