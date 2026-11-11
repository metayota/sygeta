<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');   
    $customer = $db->get('customer',['user_id'=>$user_id]);
    $presentations = $db->getAll('object_presentation',['customer_id'=>$customer['id']]);
    $dateFormat = translate('php_date_format');
            
    foreach($presentations as &$presentation) {
        $timeFormatted = date($dateFormat, strtotime($presentation['presentation_time']));
        $presentation['text'] = translate('object_presentation_text',['time'=>$timeFormatted]);
    }
    echo json_encode($presentations);
?>