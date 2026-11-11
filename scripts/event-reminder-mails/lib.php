<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  

// Add the required include for timezone functionality
include_once('../contacts-lib/contacts-lib.php');

// Function to send event reminders
function sendEventReminders() {
    global $db;
    
    // Get all event dates that are in the future or happening now
    $eventDates = $db->getAllByQuery(
        "SELECT ed.*, e.event_type, e.title, e.description, e.name 
         FROM event_date ed 
         JOIN event e ON ed.event_id = e.id 
         WHERE ed.start_date >= NOW()", 
        []
    );
    
    foreach ($eventDates as $eventDate) {
        echo "Processing event date: " . $eventDate['start_date'] . " for event: " . $eventDate['title'] . "\n";
        
        // Get the current reminder that should be sent for this event date
        $currentReminder = getCurrentReminderForEventDate($eventDate);
        
        if ($currentReminder) {
            echo "Current reminder to send: " . $currentReminder['mail_template'] . "\n";
            
            // Get participants who haven't received this specific reminder yet
            $participantsToRemind = getParticipantsNeedingCurrentReminder($eventDate['id'], $currentReminder['id']);
            
            foreach ($participantsToRemind as $participant) {
                // Get contact details
                $contact = $db->getById('contact', $participant['contact_id']);
                
                if ($contact && $contact['email']) {
                    // Collect additional variables for the email template
                    $emailVars = collectEmailVariables($eventDate, $contact, $participant, $currentReminder);
                    
                    // Send the reminder email
                    sendReminderEmail($contact, $currentReminder['mail_template'], $emailVars);
                    
                    // Update participant record to mark this reminder as sent
                    updateParticipantReminder($participant['id'], $currentReminder['id']);
                    
                    echo "Sent reminder to: " . $contact['email'] . "\n";
                }
            }
        } else {
            echo "No reminder due for this event date yet.\n";
        }
    }
}

// Get the current reminder that should be sent for this event date
function getCurrentReminderForEventDate($eventDate) {
    global $db;
    
    $sql = "
        SELECT er.* 
        FROM event_reminder er
        WHERE er.event_type = :event_type
        AND (
            (er.time_unit = 'minutes' AND DATE_SUB(:event_start, INTERVAL er.time_before_event MINUTE) <= NOW()) OR
            (er.time_unit = 'hours' AND DATE_SUB(:event_start, INTERVAL er.time_before_event HOUR) <= NOW()) OR
            (er.time_unit = 'days' AND DATE_SUB(:event_start, INTERVAL er.time_before_event DAY) <= NOW()) OR
            (er.time_unit = 'weeks' AND DATE_SUB(:event_start, INTERVAL er.time_before_event WEEK) <= NOW())
        )
        ORDER BY er.time_before_event ASC
        LIMIT 1
    ";
    
    $args = [
        'event_type' => $eventDate['event_type'],
        'event_start' => $eventDate['start_date']
    ];
    
    return $db->getByQuery($sql, $args);
}

// Get participants who need the current reminder (haven't received this specific reminder)
function getParticipantsNeedingCurrentReminder($eventDateId, $currentReminderId) {
    global $db;
    
    $sql = "
        SELECT ep.* 
        FROM event_participant ep
        WHERE ep.event_date_id = :event_date_id
        AND (ep.reminder IS NULL OR ep.reminder != :reminder_id)
    ";
    
    $args = [
        'event_date_id' => $eventDateId,
        'reminder_id' => $currentReminderId
    ];
    
    return $db->getAllByQuery($sql, $args);
}

// Updated collectEmailVariables function with timezone conversion for display only
function collectEmailVariables($eventDate, $contact, $participant, $reminder) {
    global $db;
    
    // Get the full event details
    $event = $db->getById('event', $eventDate['event_id']);
    
    // Get contact's timezone for display formatting
    $timezone = getTimezoneForContact($contact['id']);
    
    // Format dates according to contact's language and timezone
    $phpDatetimeFormat = translateLanguage($contact['language'], 'php_datetime_format');
    $phpDateFormat = translateLanguage($contact['language'], 'php_date_format');
    $phpTimeFormat = translateLanguage($contact['language'], 'php_time_format');
    
    // Convert UTC times to contact's timezone for display only
    $utcStartDate = new \DateTime($eventDate['start_date'], new \DateTimeZone('UTC'));
    $utcStartDate->setTimezone(new \DateTimeZone($timezone));
    
    $utcEndDate = new \DateTime($eventDate['end_date'], new \DateTimeZone('UTC'));
    $utcEndDate->setTimezone(new \DateTimeZone($timezone));
    
    $formattedDateTime = $utcStartDate->format($phpDatetimeFormat ?: 'Y-m-d H:i');
    $formattedDate = $utcStartDate->format($phpDateFormat ?: 'Y-m-d');
    $formattedStartTime = $utcStartDate->format($phpTimeFormat ?: 'H:i');
    $formattedEndTime = $utcEndDate->format($phpTimeFormat ?: 'H:i');
    
    // Calculate duration (this stays the same as it's a time difference)
    $startTimestamp = strtotime($eventDate['start_date']);
    $endTimestamp = strtotime($eventDate['end_date']);
    $durationMinutes = ($endTimestamp - $startTimestamp) / 60;
    
    // Translate event title and description
    $eventTitle = translateLanguage($contact['language'], $event['title']) ?: $event['title'];
    $eventDescription = translateLanguage($contact['language'], $event['description']) ?: $event['description'];
    
    // Get intelligent time until event (keep original UTC-based calculation)
    $timeUntilEvent = formatTimeUntilEvent($eventDate['start_date'], $contact['language']);
    
    return [
        // Event information
        'event_title' => $eventTitle,
        'event_description' => $eventDescription,
        'event_name' => $event['name'],
        'event_type' => $event['event_type'],
        
        // Date and time information (now in contact's timezone for display)
        'event_date' => $formattedDate,
        'event_time' => $formattedStartTime,
        'event_datetime' => $formattedDateTime,
        'event_start_time' => $formattedStartTime,
        'event_end_time' => $formattedEndTime,
        'event_duration' => $durationMinutes,
        'event_duration_hours' => floor($durationMinutes / 60),
        'event_duration_minutes' => $durationMinutes % 60,
        
        // Participant specific
        'join_link' => $participant['join_link'],
        'participant_id' => $participant['id'],
        
        // Reminder specific
        'reminder_time_before' => $reminder['time_before_event'],
        'reminder_time_unit' => $reminder['time_unit'],
        
        // Intelligent time until event (UTC-based calculation)
        'time_until_event' => $timeUntilEvent,
    ];
}

// Function to calculate and format time until event intelligently
// Keep original UTC-based logic - no timezone conversion needed here
function formatTimeUntilEvent($eventStartDate, $contactLanguage) {
    $now = time();
    $eventStart = strtotime($eventStartDate);
    $timeUntilEvent = $eventStart - $now;
    
    if ($timeUntilEvent <= 0) {
        return translateLanguage($contactLanguage, 'event_starting_now');
    }
    
    // Convert to different units
    $totalMinutes = floor($timeUntilEvent / 60);
    $totalHours = floor($timeUntilEvent / 3600);
    $totalDays = floor($timeUntilEvent / 86400);
    $totalWeeks = floor($timeUntilEvent / 604800);
    
    // Calculate remaining time after extracting larger units
    $remainingDays = $totalDays % 7;
    $remainingHours = $totalHours % 24;
    $remainingMinutes = $totalMinutes % 60;
    
    // Determine the appropriate format based on time remaining
    if ($totalWeeks > 0) {
        // Round up if close to next day
        if ($remainingHours >= 12) {
            $remainingDays++;
            if ($remainingDays >= 7) {
                $totalWeeks++;
                $remainingDays = 0;
            }
        }
        
        if ($remainingDays == 0) {
            // Only weeks
            $translationKey = $totalWeeks == 1 ? 'event_time_until_week' : 'event_time_until_weeks';
            return translateLanguage($contactLanguage, $translationKey, ['weeks' => $totalWeeks]);
        } else {
            // Weeks and days
            $weekKey = $totalWeeks == 1 ? 'week' : 'weeks';
            $dayKey = $remainingDays == 1 ? 'day' : 'days';
            $translationKey = 'event_time_until_' . $weekKey . '_' . $dayKey;
            
            return translateLanguage($contactLanguage, $translationKey, [
                'weeks' => $totalWeeks,
                'days' => $remainingDays
            ]);
        }
    } elseif ($totalDays > 0) {
        // Round up if close to next hour (12+ hours = next day)
        if ($remainingHours >= 12) {
            $totalDays++;
        }
        
        $translationKey = $totalDays == 1 ? 'event_time_until_day' : 'event_time_until_days';
        return translateLanguage($contactLanguage, $translationKey, ['days' => $totalDays]);
    } elseif ($totalHours > 0) {
        // Round up if close to next minute (30+ minutes = next hour)
        if ($remainingMinutes >= 30) {
            $totalHours++;
        }
        
        $translationKey = $totalHours == 1 ? 'event_time_until_hour' : 'event_time_until_hours';
        return translateLanguage($contactLanguage, $translationKey, ['hours' => $totalHours]);
    } else {
        // Minutes only
        $totalMinutes = max(1, $totalMinutes); // At least 1 minute
        $translationKey = $totalMinutes == 1 ? 'event_time_until_minute' : 'event_time_until_minutes';
        return translateLanguage($contactLanguage, $translationKey, ['minutes' => $totalMinutes]);
    }
}

// Send the reminder email
function sendReminderEmail($contact, $mailTemplate, $emailVars) {
    $sender = new MailContactSender();
    $sender->addContact($contact['id']); // This automatically sets basic contact/partner variables
    $sender->setTemplate($mailTemplate, $emailVars); // This adds our additional event-specific variables
    $sender->send();
}

// Update participant record to mark reminder as sent
function updateParticipantReminder($participantId, $reminderId) {
    global $db;
    
    $db->update('event_participant', 
        ['reminder' => $reminderId], 
        ['id' => $participantId]
    );
}

?>