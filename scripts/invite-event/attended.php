<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');   

    include_once('../access-check/access-check.php');
    checkWritePermissions('contact', $data['contact_id']);
    $db->update('event_participant',['attended'=>1],['contact_id'=>$data['contact_id']]);
    success('confirm_participation_done');
?>