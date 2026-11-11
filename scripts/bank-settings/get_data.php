<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

    $sql = "SELECT pm.*, t.translation as title_translated FROM payment_method pm LEFT JOIN translation t ON t.translation_key=pm.title AND t.language=:language";
    $paymentMethods = $db->getAllByQuery($sql, ['language'=>$GLOBALS['language']]);

    $companyPaymentMethod = $db->get('company_payment_method');
    $companyPaymentMethod['payment_method_data'] = json_decode($companyPaymentMethod['payment_method_data']);
    $paymentMethod = $db->getByName('payment_method', $companyPaymentMethod['payment_method']);
    echo json_encode(['companyPaymentMethod'=>$companyPaymentMethod,'paymentMethod'=>$paymentMethod, 'paymentMethods'=>$paymentMethods]);
?>