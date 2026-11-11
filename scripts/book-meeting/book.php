<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  
    include('../book-meeting/lib.php');
    include_once('../contacts-lib/contacts-lib.php');

    if (!empty($data['access_key'])) {
        $contact = $db->get('contact', ['access_key'=>$data['access_key']]);
    } else {
        $contact = $db->getById('contact', $_SESSION['loggedInUser']['contact_id']);
    }

    $partner = $db->getById('partner',$contact['partner_id']);
    $partnerContact = $db->getById('contact',$partner['contact_id']);
    $meetingInvitation = $db->getById('meeting_invitation', $data['meeting_invitation_id']);

    if (empty($meetingInvitation) && empty($user_id)) {
        errorMsg('msg_meeting_invitation_outdated');
    }

    // Convert submitted datetime from contact's timezone to UTC for database storage
    $contactTimezone = getTimezoneForContact($contact['id']);
    $submittedDateTime = new \DateTime($data['date'].' '.$data['time'], new \DateTimeZone($contactTimezone));
    $submittedDateTime->setTimezone(new \DateTimeZone('UTC'));
    $utcMeetingTime = $submittedDateTime->format('Y-m-d H:i:s');

    $meetingDuration = $meetingType['duration'];
    if (!empty($meetingInvitation['meeting_data'])) {
        $meetingData = json_decode($meetingInvitation['meeting_data'], true);
        if (!empty($meetingData['duration_minutes'])) {
            $meetingDuration = $meetingData['duration_minutes'];
        }
    }

    $meetingDataRow = [
        'duration' => $meetingDuration,
        'meeting_data' => empty($meetingInvitation) ? [] : $meetingInvitation['meeting_data'], 
        'meeting_time' => $utcMeetingTime, 
        'meeting_type_id' => $data['meeting_type_id'], 
        'organizer_contact_id' => $partnerContact['id']
    ];

    $db->insert('meeting', $meetingDataRow);
    $meetingID = $db->lastInsertId();
    $db->insert('meeting_participant', ['contact_id'=>$contact['id'], 'confirmed'=>true, 'attended'=>false, 'meeting_id'=>$meetingID]);
    $db->insert('meeting_participant', ['contact_id'=>$partnerContact['id'], 'confirmed'=>true, 'attended'=>false, 'meeting_id'=>$meetingID]);

    if (!empty($meetingInvitation)) {
        $meetingInvitation['accepted'] = true;
        $meetingInvitation['meeting_id'] = $meetingID;
        $db->update('meeting_invitation', $meetingInvitation);
    }

    // Format datetime for contact's timezone
    $phpDatetimeFormat = translateLanguage($contact['language'], 'php_datetime_format');
    $utcDate = new \DateTime($utcMeetingTime, new \DateTimeZone('UTC'));
    $utcDate->setTimezone(new \DateTimeZone($contactTimezone));
    $formattedDateTime = $utcDate->format($phpDatetimeFormat);
    
    $phpDateFormat = translateLanguage($contact['language'], 'php_date_format');
    $formattedDate = $utcDate->format($phpDateFormat);
    
    $meetingType = $db->getById('meeting_type', $data['meeting_type_id']);
    $vars = [
        'agent_name' => $partner['full_name'],
        'contact_firstname' => $contact['firstname'],
        'contact_lastname' => $contact['lastname'],
        'customer_full_name' => $contact['full_name'],
        'meeting_time' => $formattedDateTime,
        'meeting_date' => $formattedDate,
        'agent_phone' => $partner['phone'],
        'duration' => $meetingType['duration'],
        'meeting_type' => translate($meetingType['title'])
    ];

    if (!empty($meetingInvitation['meeting_data'])) {
        $meetingData = json_decode($meetingInvitation['meeting_data'], true);
        if (!empty($meetingData['duration'])) {
            $vars['duration'] = $meetingData['duration'];
        }
        $vars = array_merge($vars, $meetingData);
    }

    if (!empty($meetingType['meeting_processor'])) {
        $meetingProcessorGetVars = '../'.$meetingType['meeting_processor'].'/get_mail_variables.php';
        if (file_exists($meetingProcessorGetVars)) {
            $meeting = $db->getById('meeting', $meetingID);
            $newVars = callWithVariables($meetingProcessorGetVars, ['contact'=>$contact, 'meeting'=> $meeting]);
            $vars = array_merge($newVars, $vars);
        }
    }

    // Prepare timezone-specific variables for partner contact
    $partnerTimezone = getTimezoneForContact($partnerContact['id']);
    $phpDatetimeFormatPartner = translateLanguage($partnerContact['language'], 'php_datetime_format');
    $phpDateFormatPartner = translateLanguage($partnerContact['language'], 'php_date_format');
    
    $utcDatePartner = new \DateTime($utcMeetingTime, new \DateTimeZone('UTC'));
    $utcDatePartner->setTimezone(new \DateTimeZone($partnerTimezone));
    $formattedDateTimePartner = $utcDatePartner->format($phpDatetimeFormatPartner);
    $formattedDatePartner = $utcDatePartner->format($phpDateFormatPartner);
    
    $partnerVars = $vars;
    $partnerVars['meeting_time'] = $formattedDateTimePartner;
    $partnerVars['meeting_date'] = $formattedDatePartner;

    $confirmationMailTemplate = !empty($meetingType['confirmation_mail_template']) ? $meetingType['confirmation_mail_template'] : 'meeting_booked';
    $confirmationMailTemplatePartner = !empty($meetingType['confirmation_mail_template_partner']) ? $meetingType['confirmation_mail_template_partner'] : 'meeting_booked_partner';

    // Send email to contact with their timezone
    $sender = new MailContactSender();
    $sender->addContact($contact['id']);
    $sender->setTemplate($confirmationMailTemplate, $vars);
    $sender->send();

    // Send email to partner contact with their timezone
    $sender = new MailContactSender();
    $sender->addContact($partnerContact['id']);
    $sender->setTemplate($confirmationMailTemplatePartner, $partnerVars);
    $sender->send();

    success('meeting_booked_success');
?>