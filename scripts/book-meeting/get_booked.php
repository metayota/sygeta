<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  
include_once('../contacts-lib/contacts-lib.php');

if (!empty($data['access_key'])) {
    $contact = $db->get('contact', ['access_key'=>$data['access_key']]);
} else {
    $contact = $db->getById('contact', $_SESSION['loggedInUser']['contact_id']);
}

$meetingsSQL = "SELECT m.*
FROM meeting m
LEFT JOIN meeting_participant mp ON mp.meeting_id = m.id
LEFT JOIN meeting_type mt ON mt.id = m.meeting_type_id
WHERE mp.contact_id = :contact_id
AND mt.name = :meeting_type
AND m.meeting_time >= NOW() - INTERVAL 1 HOUR";
$meetings = $db->getAllByQuery($meetingsSQL,['contact_id'=>$contact['id'], 'meeting_type'=>$data['meeting_type']]);

$dateFormat = translate('php_datetime_format');

// Get the contact's timezone
$contactTimezone = getTimezoneForContact($contact['id']);

foreach($meetings as &$meeting) {
    // Skip if meeting_time is empty or null
    if (empty($meeting['meeting_time'])) {
        $meeting['text'] = translate('meeting_booked_text', ['time' => '']);
        continue;
    }
    
    try {
        // Create DateTime object in UTC
        $utcDate = new \DateTime($meeting['meeting_time'], new \DateTimeZone('UTC'));
        
        // Convert to contact's timezone
        $utcDate->setTimezone(new \DateTimeZone($contactTimezone));
        
        // Format the date in the contact's timezone
        $timeFormatted = $utcDate->format($dateFormat);
        
        $meeting['text'] = translate('meeting_booked_text', ['time' => $timeFormatted]);
    } catch (\Exception $e) {
        // Handle invalid datetime or timezone
        $meeting['text'] = translate('meeting_booked_text', ['time' => $meeting['meeting_time']]);
    }
}

echo json_encode($meetings);
?>