<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-helper/mail-helper.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  
    include('../create-contact/lib.php');
    include('../jitsi-lib/jitsi-lib.php');
    include_once('../contacts-lib/contacts-lib.php');
    
    // Fetch event details
    $event = $db->getById('event', $data['event_id']);
    $eventType = $db->getByName('event_type', $event['event_type']);
    if ($event) {
        // Fetch the next upcoming date for this specific event
        $eventDate = $db->getById('event_date', $data['event_date']);

        if ($eventDate) {
            // Get the partner
            $partnerUserID = stringToNumber($data['partner_code']);
            $user = $db->getById('user',$partnerUserID);
            $partner = $db->get('partner',['user_id'=>$user['id']]);

            // Create the contact
            $contact = [];
            $contact['salutation'] = $data['salutation'];
            $contact['firstname'] = $data['firstname'];
            $contact['lastname'] = $data['lastname'];
            $contact['full_name'] = $data['firstname'].' '.$data['lastname'];
            $contact['phone'] = $data['phone'];
            $contact['email'] = $data['email'];
            $contact['partner_id'] = $partner['id'];
            $contact['user_id'] = $partner['user_id'];
            $contact['timezone'] = $data['timezone'];
            $contact['id'] = createContact($contact);

            // Insert the participant into the event_participant table
            $eventParticipantData = ['event_id' => $event['id'], 'event_date_id' => $eventDate['id'], 'contact_id' => $contact['id']];
            if ($eventType['video_conferencing']) {
                $eventParticipantData['join_link'] = createJitsiMeetingShortlink($event['name'], $contact['full_name'], false);
            }
            $db->insert('event_participant', $eventParticipantData);
            $db->insert('event_invitation', ['event_id' => $event['id'], 'contact_id' => $contact['id'], 'invitation_date'=>date('Y-m-d H:i:s')]);
            
            
            // Prepare email confirmation variables with timezone conversion
            $phpDatetimeFormat = translate('php_datetime_format');
            
            // Convert UTC event date to contact's timezone for display
            $timezone = $data['timezone'];
            $utcDate = new \DateTime($eventDate['start_date'], new \DateTimeZone('UTC'));
            $utcDate->setTimezone(new \DateTimeZone($timezone));
            $displayEventDate = $utcDate->format($phpDatetimeFormat);
            
            $vars = [
                'event_name' => translate($event['title']),
                'event_description' => translate($event['description']),
                'event_date' => $displayEventDate,  // Use timezone-converted date
                'event_link' => $eventDate['event_link'],
                'participant_name' => $contact['full_name'],  // Assuming contact table has 'full_name' column
                'join_link' => $eventParticipantData['join_link']
            ];

            // Send the confirmation email
            sendMailHelper($contact['id'], $eventType['mail_template_confirmation'], $vars);
            success('event_participation_confirmed');
        } else {
            // Handle case where no upcoming date for the specific event is found
            errorMsg(translate('please_choose_event_date'));
        }
    } else {
        // Handle case where the event is not found
        errorMsg(translate('event_not_found'));
    }
?>