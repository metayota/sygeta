<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-helper/mail-helper.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  
    $invitation = $db->get('event_invitation',['invitation_code'=>$data['invitation_code']]);
    $participant = $db->get('event_participant',['contact_id'=>$invitation['contact_id'],'event_id'=>$invitation['event_id']]);
    echo json_encode(['takes_part'=>!empty($participant)]);
?>