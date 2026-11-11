<?php namespace Metayota; include_once('../metayota/library.php');  

include('../event-reminder-mails/lib.php');
include_once('../contacts-lib/contacts-lib.php'); // Added for timezone support

// Function to send meeting reminders
function sendMeetingReminders() {
    global $db;
    
    // Get all meetings that are in the future or happening now
    $meetings = $db->getAllByQuery(
        "SELECT m.*, mt.title as meeting_type_title, mt.description as meeting_type_description, mt.duration, mt.name as meeting_type_name
         FROM meeting m 
         JOIN meeting_type mt ON m.meeting_type_id = mt.id 
         WHERE m.meeting_time >= NOW()", 
        []
    );
    
    foreach ($meetings as $meeting) {
        echo "Processing meeting: " . $meeting['meeting_time'] . " for meeting type: " . $meeting['meeting_type_title'] . "\n";
        
        // Get the current reminder that should be sent for this meeting
        $currentReminder = getCurrentReminderForMeeting($meeting);
        
        if ($currentReminder) {
            echo "Current reminder to send: " . $currentReminder['mail_template'] . "\n";
            
            // Get participants who haven't received this specific reminder yet
            $participantsToRemind = getParticipantsNeedingCurrentMeetingReminder($meeting['id'], $currentReminder['id']);
            
            foreach ($participantsToRemind as $participant) {
                // Get contact details
                $contact = $db->getById('contact', $participant['contact_id']);
                
                if ($contact && $contact['email']) {
                    // Collect additional variables for the email template
                    $emailVars = collectMeetingEmailVariables($meeting, $contact, $participant, $currentReminder);
                    
                    // Send the reminder email
                    sendReminderEmail($contact, $currentReminder['mail_template'], $emailVars);
                    
                    // Update participant record to mark this reminder as sent
                    updateMeetingParticipantReminder($participant['id'], $currentReminder['id']);
                    
                    echo "Sent meeting reminder to: " . $contact['email'] . "\n";
                }
            }
        } else {
            echo "No reminder due for this meeting yet.\n";
        }
    }
}

// Get the current reminder that should be sent for this meeting - EXACT SAME AS EVENT VERSION
function getCurrentReminderForMeeting($meeting) {
    global $db;
    
    $sql = "
        SELECT mr.* 
        FROM meeting_reminder mr
        WHERE mr.meeting_type = :meeting_type
        AND (
            (mr.time_unit = 'minutes' AND DATE_SUB(:meeting_start, INTERVAL mr.time_before_event MINUTE) <= NOW()) OR
            (mr.time_unit = 'hours' AND DATE_SUB(:meeting_start, INTERVAL mr.time_before_event HOUR) <= NOW()) OR
            (mr.time_unit = 'days' AND DATE_SUB(:meeting_start, INTERVAL mr.time_before_event DAY) <= NOW()) OR
            (mr.time_unit = 'weeks' AND DATE_SUB(:meeting_start, INTERVAL mr.time_before_event WEEK) <= NOW())
        )
        ORDER BY mr.time_before_event ASC
        LIMIT 1
    ";
    
    $args = [
        'meeting_type' => $meeting['meeting_type_name'],
        'meeting_start' => $meeting['meeting_time']
    ];
    
    return $db->getByQuery($sql, $args);
}

// Get participants who need the current meeting reminder - EXACT SAME AS EVENT VERSION
function getParticipantsNeedingCurrentMeetingReminder($meetingId, $currentReminderId) {
    global $db;
    
    $sql = "
        SELECT mp.* 
        FROM meeting_participant mp
        WHERE mp.meeting_id = :meeting_id
        AND (mp.reminder IS NULL OR mp.reminder != :reminder_id)
    ";
    
    $args = [
        'meeting_id' => $meetingId,
        'reminder_id' => $currentReminderId
    ];
    
    return $db->getAllByQuery($sql, $args);
}

// Collect email variables for meeting reminders - UPDATED WITH TIMEZONE SUPPORT
function collectMeetingEmailVariables($meeting, $contact, $participant, $reminder) {
    global $db;
    
    // Get contact's timezone
    $timezone = getTimezoneForContact($contact['id']);
    
    // Format dates according to contact's language
    $phpDatetimeFormat = translateLanguage($contact['language'], 'php_datetime_format');
    $phpDateFormat = translateLanguage($contact['language'], 'php_date_format');
    $phpTimeFormat = translateLanguage($contact['language'], 'php_time_format');
    
    // Convert UTC meeting time to contact's timezone
    $utcMeetingTime = new \DateTime($meeting['meeting_time'], new \DateTimeZone('UTC'));
    $utcMeetingTime->setTimezone(new \DateTimeZone($timezone));
    
    $formattedDateTime = $utcMeetingTime->format($phpDatetimeFormat ?: 'Y-m-d H:i');
    $formattedDate = $utcMeetingTime->format($phpDateFormat ?: 'Y-m-d');
    $formattedStartTime = $utcMeetingTime->format($phpTimeFormat ?: 'H:i');
    
    // Calculate end time based on duration (in contact's timezone)
    $duration = $meeting['duration'] ?? 60; // Default 60 minutes if not set
    $endTimeObj = clone $utcMeetingTime;
    $endTimeObj->add(new DateInterval("PT{$duration}M"));
    $formattedEndTime = $endTimeObj->format($phpTimeFormat ?: 'H:i');
    
    // Translate meeting type title and description
    $meetingTypeTitle = translateLanguage($contact['language'], $meeting['meeting_type_title']) ?: $meeting['meeting_type_title'];
    $meetingTypeDescription = translateLanguage($contact['language'], $meeting['meeting_type_description']) ?: $meeting['meeting_type_description'];
    
    // Get intelligent time until meeting - USING THE EXISTING FUNCTION
    $timeUntilMeeting = formatTimeUntilEvent($meeting['meeting_time'], $contact['language']);
    
    // Base email variables array
    $emailVars = [
        // Meeting information
        'meeting_id' => $meeting['id'],
        'meeting_type_id' => $meeting['meeting_type_id'],
        'meeting_type_name' => $meeting['meeting_type_name'],
        'meeting_type_title' => $meetingTypeTitle,
        'meeting_type_description' => $meetingTypeDescription,
        
        // Date and time information (now in contact's timezone)
        'meeting_date' => $formattedDate,
        'meeting_time' => $formattedStartTime,
        'meeting_datetime' => $formattedDateTime,
        'meeting_start_time' => $formattedStartTime,
        'meeting_end_time' => $formattedEndTime,
        'meeting_duration' => $duration,
        'meeting_duration_hours' => floor($duration / 60),
        'meeting_duration_minutes' => $duration % 60,
        
        // Participant specific
        'participant_id' => $participant['id'],
        
        // Reminder specific - EXACT SAME AS EVENT VERSION
        'reminder_time_before' => $reminder['time_before_event'],
        'reminder_time_unit' => $reminder['time_unit'],
        
        // Intelligent time until meeting
        'time_until_meeting' => $timeUntilMeeting,
    ];
    
    // Decode and merge meeting_data if it exists
    if (!empty($meeting['meeting_data'])) {
        $meetingData = json_decode($meeting['meeting_data'], true);
        
        // Only merge if json_decode was successful and returned an array
        if (is_array($meetingData)) {
            $emailVars = array_merge($emailVars, $meetingData);
        } else {
            // Log warning if meeting_data exists but couldn't be decoded
            error_log("Warning: Could not decode meeting_data for meeting ID " . $meeting['id']);
        }
    }
    
    return $emailVars;
}

// Update meeting participant record to mark reminder as sent
function updateMeetingParticipantReminder($participantId, $reminderId) {
    global $db;
    
    $db->update('meeting_participant', 
        ['reminder' => $reminderId], 
        ['id' => $participantId]
    );
}

?>