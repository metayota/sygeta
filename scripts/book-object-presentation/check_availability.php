<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');   
    $customer = $db->get('customer',['user_id'=>$user_id]);
    $selfDisclosure = $db->get('self_disclosure',['customer_id'=>$customer['id']]);

    $possible = false;
    if (!empty($selfDisclosure)) {
        if ($selfDisclosure['feasibility_assured'] == 1) {
            $possible = true;
        } 
    }
    echo json_encode(['possible'=>$possible,'message'=>translate('object_presentation_not_possible')]);
?>