<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');   

// TODO: Berechtigungen prüfen

$eventType = $data['event_type'];
$validEventTypes = ['calendar_event', 'meeting', 'event'];

if (in_array($eventType, $validEventTypes)) {
    $eventId = $data['id'];
    $currentUserContactId = $_SESSION['loggedInUser']['contact_id'];

    // Get event details before deletion
    $event = null;
    $participants = [];
    
    if ($eventType === 'calendar_event') {
        $event = $db->getById('calendar_event', $eventId);
        $user = $db->getById('user', $user_id);
        
        // Get all participants for this calendar event
        $participants = $db->getAllByQuery(
            'SELECT c.id, c.full_name, c.email, c.language FROM calendar_event_participant cep
             JOIN contact c ON cep.contact_id = c.id WHERE cep.calendar_event_id = :event_id',
            ['event_id' => $eventId]
        );
        
        // Check permissions: owner can always cancel, or if only 2 participants and current user is one of them
        $canCancel = false;
        if ($event['contact_id'] == $currentUserContactId) {
            // Current user is the owner
            $canCancel = true;
        } elseif (count($participants) == 2) {
            // Check if current user is one of the two participants
            foreach ($participants as $participant) {
                if ($participant['id'] == $currentUserContactId) {
                    $canCancel = true;
                    break;
                }
            }
        }
        
        if (!$canCancel) {
            errorMsg(translate('only_owner_can_cancel_event'));
        }
    } 
    elseif ($eventType === 'meeting') {
        $event = $db->getById('meeting', $eventId);
        
        // Get all participants for this meeting
        $participants = $db->getAllByQuery(
            'SELECT c.id, c.full_name, c.email, c.language FROM meeting_participant mp
             JOIN contact c ON mp.contact_id = c.id WHERE mp.meeting_id = :meeting_id',
            ['meeting_id' => $eventId]
        );
        
        // Check permissions: organizer can always cancel, or if only 2 participants and current user is one of them
        $canCancel = false;
        if ($event['organizer_contact_id'] == $currentUserContactId) {
            // Current user is the organizer
            $canCancel = true;
        } elseif (count($participants) == 2) {
            // Check if current user is one of the two participants
            foreach ($participants as $participant) {
                if ($participant['id'] == $currentUserContactId) {
                    $canCancel = true;
                    break;
                }
            }
        }
        
        if (!$canCancel) {
            errorMsg(translate('only_owner_can_cancel_event'));
        }
    }
    elseif ($eventType === 'event') {
        $eventDateId = $data['event_date_id'];
        $eventDate = $db->getById('event_date', $eventDateId);
        
        $isAdmin = hasAccess('database','event');
        
        if (!$eventDate) {
            echo json_encode(['error' => 'Event date not found']);
            exit;
        }
        
        // Get the main event for title
        $event = $db->getById('event', $eventId);
        
        // Get both participants and moderators for this event_date
        $eventParticipants = $db->getAllByQuery(
            'SELECT c.id, c.full_name, c.email, c.language FROM event_participant ep
             JOIN contact c ON ep.contact_id = c.id 
             WHERE ep.event_id = :event_id AND ep.event_date_id = :event_date_id',
            ['event_id' => $eventId, 'event_date_id' => $eventDateId]
        );
        
        $eventModerators = $db->getAllByQuery(
            'SELECT c.id, c.full_name, c.email, c.language FROM event_moderator em
             JOIN contact c ON em.contact_id = c.id 
             WHERE em.event_date_id = :event_date_id',
            ['event_date_id' => $eventDateId]
        );
        
        // Check if current user is a participant or moderator
        $userIsParticipant = false;
        foreach ($eventParticipants as $participant) {
            if ($participant['id'] == $currentUserContactId) {
                $userIsParticipant = true;
                break;
            }
        }
        
        $userIsModerator = false;
        foreach ($eventModerators as $moderator) {
            if ($moderator['id'] == $currentUserContactId) {
                $userIsModerator = true;
                break;
            }
        }
        
        // If not admin and not participant/moderator, deny access
        if (!$isAdmin && !$userIsParticipant && !$userIsModerator) {
            errorMsg(translate('no_permission_to_cancel_event'));
        }
        
        // Only send emails if admin is canceling the entire event
        if ($isAdmin) {
            $participants = array_merge($eventParticipants ?: [], $eventModerators ?: []);
        } else {
            // Regular user leaving - no emails needed
            $participants = [];
        }
    }

    if (!$event) {
        echo json_encode(['error' => 'Event not found']);
        exit;
    }

    // Get start date/time based on event type (only needed for email notifications)
    if (!empty($participants)) {
        if ($eventType === 'calendar_event') {
            $startDateTime = $event['event_start'];
        } elseif ($eventType === 'meeting') {
            $startDateTime = $event['meeting_time'];
        } elseif ($eventType === 'event') {
            $startDateTime = $eventDate['start_date'];
        }

        // Email notification (only if there are participants to notify)
        foreach ($participants as $participant) {
            if ($participant && isset($participant['id'])) {
                // Get datetime format for this participant's language
                $phpDatetimeFormat = translateLanguage($participant['language'], 'php_datetime_format');
                $formattedDate = date($phpDatetimeFormat, strtotime($startDateTime));
                
                // Determine event title for this participant's language
                if ($eventType === 'calendar_event' && !empty($event['calendar_text'])) {
                    $eventTitle = translateLanguage($participant['language'], $event['calendar_text']);
                } elseif ($eventType === 'meeting') {
                    $meetingType = $db->getById('meeting_type', $event['meeting_type_id']);
                    $eventTitle = translateLanguage($participant['language'], $meetingType['title']);
                } elseif ($eventType === 'event') {
                    $eventTitle = translateLanguage($participant['language'], $event['title'] ?? '');
                } else {
                    $eventTitle = translateLanguage($participant['language'], $eventType);
                }
                
                sendMailHelper($participant['id'], 'event_canceled', [
                    'event_title' => $eventTitle,
                    'event_time' => $formattedDate
                ]);
            }
        }
    }

    // Delete/remove from database based on user type and event type
    if ($eventType === 'event') {
        if ($isAdmin) {
            // Admin canceling: delete entire event_date and all related data
            $db->delete('event_participant', ['event_id' => $eventId, 'event_date_id' => $eventDateId]);
            $db->delete('event_moderator', ['event_date_id' => $eventDateId]);
            $db->delete('event_date', ['id' => $eventDateId]);
            
            success('event_canceled_successfully');
        } else {
            // Regular user leaving: only remove their participation (no emails)
            if ($userIsParticipant) {
                $db->delete('event_participant', [
                    'event_id' => $eventId, 
                    'event_date_id' => $eventDateId,
                    'contact_id' => $currentUserContactId
                ]);
            }
            
            if ($userIsModerator) {
                $db->delete('event_moderator', [
                    'event_date_id' => $eventDateId,
                    'contact_id' => $currentUserContactId
                ]);
            }
            
            success('event_left_successfully');
        }
    } else {
        // For calendar_event and meeting, always delete the entire event
        $db->delete($eventType, ['id' => $eventId]);
        success('event_canceled_successfully');
    }
} else {
    echo json_encode(['error' => 'Invalid event type']);
}
?>