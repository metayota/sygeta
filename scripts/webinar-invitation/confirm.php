<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-helper/mail-helper.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  
    // Fetch event details based on event_name
    $event = $db->getByName('event', $data['event_name']);

    if ($event) {
        // Fetch the next upcoming date for this specific event
        $eventDate = $db->getByQuery("SELECT * FROM event_date WHERE event_id = :event_id AND start_date > NOW() ORDER BY start_date ASC LIMIT 1", ['event_id' => $event['id']]);

        if ($eventDate) {
            // Fetch contact details using the invitation code
            $contact = $db->getByQuery("SELECT c.* FROM event_invitation wi LEFT JOIN contact c ON c.id = wi.contact_id WHERE wi.invitation_code = :invitation_code", ['invitation_code' => $data['invitation_code']]);

            // Insert the participant into the event_participant table
            $db->insert('event_participant', ['event_id' => $event['id'], 'contact_id' => $contact['id']]);
            
            // Prepare email confirmation variables
            $phpDatetimeFormat = translate('php_datetime_format');
            $vars = [
                'event_title' => translate($event['title']),
                'event_description' => translate($event['description']),
                'event_date' => date($phpDatetimeFormat, strtotime($eventDate['start_date'])),  // Format the date here
                'event_link' => $eventDate['event_link'],
                'participant_name' => $contact['full_name']  // Assuming contact table has 'full_name' column
            ];

            // Send the confirmation email
            sendMailHelper($contact['id'], 'event_confirmation', $vars);

            success(translate('event_participation_confirmed'));
        } else {
            // Handle case where no upcoming date for the specific event is found
            error(translate('no_upcoming_event_found'));
        }
    } else {
        // Handle case where the event is not found
        error(translate('event_not_found'));
    }
?>