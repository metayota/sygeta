<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   
// Add the include for timezone function
include_once('../contacts-lib/contacts-lib.php');
include('../jitsi-lib/jitsi-lib.php');

if ($data['event_start'] > $data['event_end']) {
    errorMsg(translate('event_end_not_before_start'));
} 

if ($data['event_end'] < date('Y-m-d H:i:s')) {
    errorMsg(translate('event_end_not_in_past'));
}

// Aktuellen Benutzer und Kontakt abrufen
$user = $db->getById('user', $user_id);
$contact = $db->getById('contact', $user['contact_id']);
$appSettings = $db->get('app_settings');

// Neues Kalenderevent hinzufügen
$data['approved'] = false;
$data['contact_id'] = $contact['id'];
$data['user_id'] = $user_id;
$participants = $data['participants'];
unset($data['participants']);
$db->insert('calendar_event', $data); // Event-ID speichern
$eventId = $db->lastInsertId();

foreach($participants as $participant) {
    if ($participant != $contact['id']) {
        $db->insert('calendar_event_participant',['calendar_event_id'=>$eventId,'contact_id'=>$participant,'user_id'=>$user_id]);
    }
}
$participantData = ['calendar_event_id'=>$eventId,'contact_id'=>$contact['id'],'user_id'=>$user_id, 'status'=>'confirmed'];

if ($data['video_conferencing']) {
    $participantData['video_meeting_link'] = createJitsiMeetingShortlink('calendar-event-'.$eventId, $contact['full_name'], false);
}
$db->insert('calendar_event_participant',$participantData);

// Teilnehmer für das neue Event finden
$participants = $db->getAllByQuery(
    'SELECT cep.id, c.full_name, c.email, cep.status, c.id as contact_id, c.language
     FROM calendar_event_participant cep
     JOIN contact c ON cep.contact_id = c.id
     WHERE cep.calendar_event_id = :event_id',
    ['event_id' => $eventId]
);

// Event-Details abrufen
$eventDetails = $db->getById('calendar_event', $eventId);

$company = $db->get('company_settings');

// E-Mails an alle Teilnehmer senden
foreach ($participants as $participant) {
    $sender = new MailContactSender();

    $accessKey = $db->getColumnById('contact', 'access_key', $participant['contact_id']);
    $acceptLink = $appSettings['app_url']."call/confirm-event/confirm?event_id={$eventId}&access_key=$accessKey&status=confirmed";
    $declineLink = $appSettings['app_url']."call/confirm-event/confirm?event_id={$eventId}&access_key=$accessKey&status=declined";

    $dateTimeFormat = translateLanguage($participant['language'], 'php_datetime_format'); // d.m.Y H:i
    $venue = empty($eventDetails['venue']) ? translateLanguage($participant['language'], 'no_venue_specified') : translateLanguage($participant['language'], $eventDetails['venue']);
    $description = empty($eventDetails['description']) ? translateLanguage($participant['language'], 'not_specified') : translateLanguage($participant['language'], $eventDetails['description']);
    
    // TIMEZONE CONVERSION - Convert UTC times to participant's timezone for email display
    $timezone = getTimezoneForContact($participant['contact_id']);
    
    // Convert event start time
    $utcStartDate = new \DateTime($eventDetails['event_start'], new \DateTimeZone('UTC'));
    $utcStartDate->setTimezone(new \DateTimeZone($timezone));
    $localEventStart = $utcStartDate->format($dateTimeFormat);
    
    // Convert event end time
    $utcEndDate = new \DateTime($eventDetails['event_end'], new \DateTimeZone('UTC'));
    $utcEndDate->setTimezone(new \DateTimeZone($timezone));
    $localEventEnd = $utcEndDate->format($dateTimeFormat);

    // E-Mail-Vorlage ausfüllen
    $emailVariables = [
        'event_title' => translateLanguage($participant['language'], $eventDetails['calendar_text']),
        'participant_name' => $participant['full_name'],
        'event_start' => $localEventStart,  // Now in participant's timezone
        'event_end' => $localEventEnd,      // Now in participant's timezone
        'event_venue' => $venue,
        'conference_url' => $eventDetail['conference_url'],
        'event_description' => $description,
        'participant_status' => translateLanguage($participant['language'], 'event_status_'.$participant['status']),
        'organization_name' => $company['name'],
        'contact_email' => $company['email'],
        'contact_phone' => $company['phone'],
        'accept_link' => $acceptLink,
        'decline_link' => $declineLink,
        'inviter_full_name' => $contact['full_name']
    ];

    if ($participant['contact_id'] != $contact['id']) {
        // Teilnehmer zur E-Mail hinzufügen und senden
        $sender->addContact($participant['contact_id']);
        $sender->setTemplate('calendar_event_invitation', $emailVariables);
        $sender->send();
    }
}
success('calendar_added_success');
?>