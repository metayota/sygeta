<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  
    $deal = $db->getById('closed_deal', $data['closed_deal_id']);
    echo json_encode($deal);
?>