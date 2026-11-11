<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../mail-helper/mail-helper.php');  
// Add the include for timezone function
include_once('../contacts-lib/contacts-lib.php');
include_once('../access-check/access-check.php');
checkWritePermissions('calendar_event', $data['id']);


// Aktuellen Benutzer und Kontakt abrufen
$user = $db->getById('user', $user_id);
$contact = $db->getById('contact', $user['contact_id']);
$appSettings = $db->get('app_settings');
$company = $db->get('company_settings');

// Event-ID aus den Daten extrahieren
$eventId = $data['id'];

// Participants aus data extrahieren und entfernen (falls vorhanden)
//$participants = isset($data['participants']) ? $data['participants'] : null;
unset($data['participants']);

// Update the calendar event in the database
unset($data['video_conferencing']);
$db->update('calendar_event', $data, ['id' => $eventId]);

// Get existing participants for the event
$existingParticipants = $db->getAllByQuery(
    'SELECT cep.id, c.full_name, c.email, cep.status, c.id as contact_id, c.language
     FROM calendar_event_participant cep
     JOIN contact c ON cep.contact_id = c.id
     WHERE cep.calendar_event_id = :event_id',
    ['event_id' => $eventId]
);

// Event-Details nach Update abrufen
$eventDetails = $db->getById('calendar_event', $eventId);

success('calendar_updated_success');

// E-Mails an alle bestehenden Teilnehmer senden (Event-Update-Benachrichtigung)
foreach ($existingParticipants as $participant) {
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

    // E-Mail-Vorlage für Event-Update ausfüllen
    $emailVariables = [
        'event_title' => translateLanguage($participant['language'], $eventDetails['calendar_text']),
        'participant_name' => $participant['full_name'],
        'event_start' => $localEventStart,  // Now in participant's timezone
        'event_end' => $localEventEnd,      // Now in participant's timezone
        'event_venue' => $venue,
        'event_description' => $description,
        'participant_status' => translateLanguage($participant['language'], 'event_status_'.$participant['status']),
        'organization_name' => $company['name'],
        'contact_email' => $company['email'],
        'contact_phone' => $company['phone'],
        'accept_link' => $acceptLink,
        'decline_link' => $declineLink,
        'inviter_full_name' => $contact['full_name']
    ];

    // Skip sending email to the event creator (same logic as add script)
    if ($participant['contact_id'] != $contact['id']) {
        // Teilnehmer zur E-Mail hinzufügen und senden (Event-Update-Template verwenden)
        $sender->addContact($participant['contact_id']);
        $sender->setTemplate('calendar_event_updated', $emailVariables); // Unterschiedliches Template für Updates
        $sender->send();
    }
}
?>