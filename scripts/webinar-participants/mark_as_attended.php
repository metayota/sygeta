<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   

    $db->update('event_participant',['attended'=>1],['contact_id'=>$data['contact_id']]);
    $db->update('customer',['current_step'=>'fill_out_self_disclosure'],['contact_id'=>$data['contact_id']]);
    success('confirm_participation_done');

?>