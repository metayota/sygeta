<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../php-mailer/php-mailer.php');   
    $db->update('event_participant',['attended'=>1],['contact_id'=>$data['contact_id']]);
    success(translate('confirm_participation_done'));
?>