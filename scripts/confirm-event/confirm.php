<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   
    include('../jitsi-lib/jitsi-lib.php');
    // Add the include for timezone function and contacts-lib
    include_once('../contacts-lib/contacts-lib.php');

    $eventID = $_GET['event_id'];
    $accessKey = $_GET['access_key'];
    $contact = $db->get('contact', ['access_key'=>$accessKey]);

    if (!empty($contact)) {
        $participant = $db->get('calendar_event_participant',['calendar_event_id'=>$eventID, 'contact_id'=>$contact['id']]);
        $participant['status'] = $_GET['status'];

        header('Location: /confirm-event/'.$participant['id'].'#key='.$accessKey);

        $event = $db->getById('calendar_event', $eventID); // Fixed: should be 'calendar_event' not 'event'
        if ($event['video_conferencing'] && $_GET['status'] == 'confirmed') {
            $participant['video_meeting_link'] = createJitsiMeetingShortlink('calendar-event-'.$event['id'], $contact['full_name'], false);
            
            // Send confirmation email with video meeting details
            $sender = new MailContactSender();
            $appSettings = $db->get('app_settings');
            $company = $db->get('company_settings');
            
            // Get timezone for participant
            $timezone = getTimezoneForContact($contact['id']);
            $dateTimeFormat = translateLanguage($contact['language'], 'php_datetime_format');
            
            // Convert UTC times to participant's timezone
            $utcStartDate = new \DateTime($event['event_start'], new \DateTimeZone('UTC'));
            $utcStartDate->setTimezone(new \DateTimeZone($timezone));
            $localEventStart = $utcStartDate->format($dateTimeFormat);
            
            $utcEndDate = new \DateTime($event['event_end'], new \DateTimeZone('UTC'));
            $utcEndDate->setTimezone(new \DateTimeZone($timezone));
            $localEventEnd = $utcEndDate->format($dateTimeFormat);
            
            // Prepare venue and description
            $venue = empty($event['venue']) ? translateLanguage($contact['language'], 'no_venue_specified') : translateLanguage($contact['language'], $event['venue']);
            $description = empty($event['description']) ? translateLanguage($contact['language'], 'not_specified') : translateLanguage($contact['language'], $event['description']);
            
            // Email variables for video meeting confirmation
            $emailVariables = [
                'event_title' => translateLanguage($contact['language'], $event['calendar_text']),
                'participant_name' => $contact['full_name'],
                'event_start' => $localEventStart,
                'event_end' => $localEventEnd,
                'event_venue' => $venue,
                'conference_url' => $event['conference_url'], // Fixed typo from original code
                'event_description' => $description,
                'participant_status' => translateLanguage($contact['language'], 'event_status_'.$participant['status']),
                'organization_name' => $company['name'],
                'contact_email' => $company['email'],
                'contact_phone' => $company['phone'],
                'video_meeting_link' => $participant['video_meeting_link'], // New variable for video link
                'inviter_full_name' => $contact['full_name'] // You might want to get the actual inviter instead
            ];
            
            // Send email
            $sender->addContact($contact['id']);
            $sender->setTemplate('calendar_event_video_meeting_confirmed', $emailVariables);
            $sender->send();
        }
        
        $db->update('calendar_event_participant',$participant);
    }
?>