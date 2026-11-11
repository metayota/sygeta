<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../translation-service/translation-service.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');
    include('../close-deal/lib.php');
    $result = bookDeal($data['closed_deal_id']);
    echo json_encode($result);
?>