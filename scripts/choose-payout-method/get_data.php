<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

    $sql = "SELECT pm.*, t.translation as title_translated FROM payout_method pm LEFT JOIN translation t ON t.translation_key=pm.title AND t.language=:language";
    $payoutMethods = $db->getAllByQuery($sql, ['language'=>$GLOBALS['language']]);

    $partner = $db->get('partner', ['user_id'=>$user_id]);
    $currentPayoutMethod = $db->get('partner_payout_method', ['partner_id'=>$partner['id']]);
    $currentPayoutMethod['payout_data'] = json_decode($currentPayoutMethod['payout_data']);
    $payoutMethod = $db->getById('payout_method', $currentPayoutMethod['payout_method_id']);
    echo json_encode(['partnerPayoutMethod'=>$currentPayoutMethod,'payoutMethod'=>$payoutMethod, 'payoutMethods'=>$payoutMethods]);
?>