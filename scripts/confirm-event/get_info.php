<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   
    include_once('../contacts-lib/contacts-lib.php');
    
    $participant = $db->getById('calendar_event_participant', $data['participant_id']);
    $event = $db->getById('calendar_event', $participant['calendar_event_id']);
    $contact = $db->getById('contact', $participant['contact_id']);

    $contactIDFromKey = $db->getColumn('contact', 'id', ['access_key'=>$data['access_key']]);
    if ($contact['id'] != $contactIDFromKey) {
        var_dump($contact);
        var_dump($contactIDFromKey);
        http_response_code(403);
        echo json_encode(['message'=>'Access key not correct!','highlight'=>'db-list-data','resource'=>$data['table']]);
        die();
    }
    
    $contact['salutation'] = translate($contact['salutation']);
    
    // Get timezone and datetime format for the contact
    $timezone = getTimezoneForContact($contact['id']);
    //die($timezone);
    $dateTimeFormat = translateLanguage($contact['language'], 'php_datetime_format');
    
    // Event-Daten formatieren
    if ($event) {
        // Datum und Zeit für Start formatieren
        if (!empty($event['event_start'])) {
            $utcDate = new \DateTime($event['event_start'], new \DateTimeZone('UTC'));
            $utcDate->setTimezone(new \DateTimeZone($timezone));
            $event['event_start'] = $utcDate->format($dateTimeFormat);
        }
        
        // Datum und Zeit für Ende formatieren
        if (!empty($event['event_end'])) {
            $utcDate = new \DateTime($event['event_end'], new \DateTimeZone('UTC'));
            $utcDate->setTimezone(new \DateTimeZone($timezone));
            $event['event_end'] = $utcDate->format($dateTimeFormat);
        }
        
        // event_day formatieren (falls noch separat verwendet)
        if (!empty($event['event_day'])) {
            $utcDate = new \DateTime($event['event_day'], new \DateTimeZone('UTC'));
            $utcDate->setTimezone(new \DateTimeZone($timezone));
            $event['event_day'] = $utcDate->format('d.m.Y');
        }
        
        // Venue-Fallback falls leer - gleicher Key wie im E-Mail Code
        if (empty($event['venue'])) {
            $event['venue'] = translate('no_venue_specified');
        } else {
            $event['venue'] = translate($event['venue']);
        }
        $event['calendar_text'] = translate($event['calendar_text']);
        $event['description'] = translate($event['description']);
    }
    
    echo json_encode([
        'participant' => $participant,
        'event' => $event,
        'contact' => $contact
    ]);
?>