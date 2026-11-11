<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../contacts-lib/contacts-lib.php');   include_once('../translation-service/translation-service.php');   
    include_once('../access-check/access-check.php');
    checkReadPermissions('contact', $data['contact_id']);

    $contact = getContactById($data['contact_id']);

    $meetingType = $db->getByName('meeting_type', $data['meeting_type']);
    $meetingType['title'] = translate($meetingType['title']);

    $resource = $db->getByName('resource', $meetingType['meeting_form']);
    $formTitle = translate($resource['title']);
    $formDescription = translate($resource['documentation']);

    echo json_encode(['contact'=>$contact, 'meetingType'=>$meetingType, 'formTitle'=>$formTitle, 'formDescription'=>$formDescription]);
?>