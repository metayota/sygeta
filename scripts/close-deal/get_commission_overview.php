<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  
    include_once('../close-deal/lib.php');
    $closedDeal = calculateCommissionsForDeal($data['closed_deal_id']);
    echo json_encode($closedDeal);
?>