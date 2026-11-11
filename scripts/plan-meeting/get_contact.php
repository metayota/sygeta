<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../contacts-lib/contacts-lib.php');   include_once('../translation-service/translation-service.php');   
    $contact = getContactById($data['contact_id']);

    $meetingType = $db->getByName('meeting_type', $data['meeting_type']);
    $meetingType['title'] = translate($meetingType['title']);

    echo json_encode(['contact'=>$contact, 'meetingType'=>$meetingType]);
?>