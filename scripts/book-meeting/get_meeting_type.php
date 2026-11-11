<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  
    $meetingType = $db->getByName('meeting_type', $data['meeting_type']);
    $meetingType['booking_title'] = translate($meetingType['booking_title']);
    $meetingType['booking_description'] = translate($meetingType['description']);
    echo json_encode($meetingType);
?>