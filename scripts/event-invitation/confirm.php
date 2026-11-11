<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-helper/mail-helper.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  
    include('../jitsi-lib/jitsi-lib.php');
    include_once('../contacts-lib/contacts-lib.php');
    
    // Fetch event details based on event_name
    $event = $db->getByName('event', $data['event_name']);
    $eventType = $db->getByName('event_type', $event['event_type']);

    if ($event) {
        // Fetch the next upcoming date for this specific event
        $eventDate = $db->getById('event_date', $data['event_date']);

        if ($eventDate) {
            // Fetch contact details using the invitation code
            $contact = $db->getByQuery("SELECT c.* FROM event_invitation wi LEFT JOIN contact c ON c.id = wi.contact_id WHERE wi.invitation_code = :invitation_code", ['invitation_code' => $data['invitation_code']]);
            if (empty($contact)) {
                header('Location: /');
                die();
            }

            if ($contact['timezone'] != $data['timezone']) {
                $db->update('contact', ['timezone'=>$data['timezone']], ['id'=>$contact['id']]);
            }

            // Insert the participant into the event_participant table
            $eventParticipantData = ['event_id' => $event['id'], 'contact_id' => $contact['id'], 'event_date_id'=>$data['event_date']];
            if ($eventType['video_conferencing']) {
                $eventParticipantData['join_link'] = createJitsiMeetingShortlink($event['name'], $contact['full_name'], false);
            }
            $db->insert('event_participant', $eventParticipantData );

            // Prepare email confirmation variables with timezone conversion
            $phpDatetimeFormat = translate('php_datetime_format');
            
            // Convert UTC datetime to user's timezone for display
            $timezone = $data['timezone']; // Using the timezone from $data as specified
            $utcDate = new \DateTime($eventDate['start_date'], new \DateTimeZone('UTC'));
            $utcDate->setTimezone(new \DateTimeZone($timezone));
            $displayDate = $utcDate->format($phpDatetimeFormat);
            
            $vars = [
                'event_name' => translate($event['title']),
                'event_description' => translate($event['description']),
                'event_date' => $displayDate,  // Using timezone-converted date
                'participant_name' => $contact['full_name'],  // Assuming contact table has 'full_name' column
                'join_link' => $eventParticipantData['join_link']
            ];

            // Send the confirmation email
            sendMailHelper($contact['id'], $eventType['mail_template_confirmation'], $vars);

            success('event_participation_confirmed');
        } else {
            // Handle case where no upcoming date for the specific event is found
            error(translate('no_upcoming_event_found'));
        }
    } else {
        // Handle case where the event is not found
        error(translate('event_not_found'));
    }
?>