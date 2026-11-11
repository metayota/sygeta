<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  
function containsHtml($string) {
    if (empty($string)) {
        return false;
    }
    $strippedString = strip_tags($string);
    return $strippedString !== $string;
}

function getEventsForUser($userID, $timezone = null) {
    global $db;
    // Get calendar events
    $user = $db->getById('user', $userID);
    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    $phpDateFormat = translate('php_date_format');
    $phpTimeFormat = translate('php_time_format');
    
    $events = $db->getAllByQuery(
    'SELECT DISTINCT ce.*, cep.video_meeting_link
    FROM calendar_event ce
    LEFT JOIN calendar_event_participant cep ON ce.id = cep.calendar_event_id
    WHERE cep.contact_id = :contact_id',
    ['contact_id' => $user['contact_id']]
    );
    
    foreach ($events as &$event) {
        $eventDescription = translate($event['description']);
        if (!empty($eventDescription)) {//&& !containsHtml($eventDescription)
            $eventDescription = formatText($eventDescription);
        }
        if (!empty($event['venue'])) {
            $event['venue'] = nl2br(translate($event['venue']));
        }
        $event['calendar_text'] = translate($event['calendar_text']);
        $event['description'] = translate($event['description']);
        $event['title'] = translate($event['calendar_text'] ?? '');
        $event['event_type'] = 'calendar_event';
        $event['preparation_time'] = !empty($event['preparation_time']) ? $event['preparation_time'] : 0;
        $event['post_buffer_time'] = !empty($event['post_buffer_time']) ? $event['post_buffer_time'] : 0;

        // Convert times to specified timezone if provided
        if ($timezone) {
            $utcStartDate = new \DateTime($event['event_start'], new \DateTimeZone('UTC'));
            $utcStartDate->setTimezone(new \DateTimeZone($timezone));
            $utcEndDate = new \DateTime($event['event_end'], new \DateTimeZone('UTC'));
            $utcEndDate->setTimezone(new \DateTimeZone($timezone));
            
            $event['start_date_formatted'] = $utcStartDate->format($phpDateFormat);
            $event['start_time_formatted'] = $utcStartDate->format($phpTimeFormat);
            $event['end_date_formatted'] = $utcEndDate->format($phpDateFormat);
            $event['end_time_formatted'] = $utcEndDate->format($phpTimeFormat);
        } else {
            $event['start_date_formatted'] = date($phpDateFormat, strtotime($event['event_start']));
            $event['start_time_formatted'] = date($phpTimeFormat, strtotime($event['event_start']));
            $event['end_date_formatted'] = date($phpDateFormat, strtotime($event['event_end']));
            $event['end_time_formatted'] = date($phpTimeFormat, strtotime($event['event_end']));
        }
        
        // Get participants with their status for the current event
        $participants = $db->getAllByQuery(
        'SELECT c.id, c.full_name, c.email, cep.status
        FROM calendar_event_participant cep
        JOIN contact c ON cep.contact_id = c.id
        WHERE cep.calendar_event_id = :event_id',
        ['event_id' => $event['id']]
        );
        $event['participants'] = $participants ?: [];
    }
    
    // Get object meetings
    $currentCustomer = $db->get('customer',['user_id'=>$userID]);
    $currentPartner = $db->get('partner',['user_id'=>$userID]);
    $meetingSQL = "SELECT m.* FROM meeting m LEFT JOIN meeting_participant mp ON m.id = mp.meeting_id WHERE mp.contact_id=:contact_id";
    if (!empty($currentPartner)) {
        $meetings = $db->getAllByQuery($meetingSQL,['contact_id'=>$currentPartner['contact_id']]) ?: [];
    } else if (!empty($currentCustomer)) {
        $meetings = $db->getAllByQuery($meetingSQL,['contact_id'=>$currentCustomer['contact_id']]) ?: [];
    } else {
        $meetings = [];
    }
    
    if (!empty($meetings)) {
        $validmeetings = [];
        
        foreach ($meetings as $meeting) {
            $meetingParticipants = $db->getAllByQuery("SELECT c.id, c.full_name, c.email, 'confirmed' as status FROM meeting_participant mp LEFT JOIN contact c ON mp.contact_id = c.id WHERE mp.meeting_id=:meeting_id", ['meeting_id'=>$meeting['id']]);
            
            $meetingType = $db->getById('meeting_type', $meeting['meeting_type_id']);
            $meetingTypeTitle = translate($meetingType['title']);
            $meetingTypeDescription = translate($meetingType['description']);
            
            $duration = $meetingType['duration'];
            if (!empty($meeting['duration']) && $meeting['duration'] != 0) {
                $duration = $meeting['duration'];
            }
            $eventEnd = date('Y-m-d H:i:s', strtotime($meeting['meeting_time'] . " + $duration minutes"));
            
            // Convert times to specified timezone if provided
            if ($timezone) {
                $utcMeetingTime = new \DateTime($meeting['meeting_time'], new \DateTimeZone('UTC'));
                $utcMeetingTime->setTimezone(new \DateTimeZone($timezone));
                $utcEndTime = new \DateTime($eventEnd, new \DateTimeZone('UTC'));
                $utcEndTime->setTimezone(new \DateTimeZone($timezone));
                
                $startDateFormatted = $utcMeetingTime->format($phpDateFormat);
                $startTimeFormatted = $utcMeetingTime->format($phpTimeFormat);
                $endDateFormatted = $utcEndTime->format($phpDateFormat);
                $endTimeFormatted = $utcEndTime->format($phpTimeFormat);
                $eventDay = $utcMeetingTime->format('Y-m-d');
                $startTime = $utcMeetingTime->format('H:i');
                $calendarText = $utcMeetingTime->format('H:i') . ' ' . $meetingTypeTitle;
            } else {
                $startDateFormatted = date($phpDateFormat, strtotime($meeting['meeting_time']));
                $startTimeFormatted = date($phpTimeFormat, strtotime($meeting['meeting_time']));
                $endDateFormatted = date($phpDateFormat, strtotime($eventEnd));
                $endTimeFormatted = date($phpTimeFormat, strtotime($eventEnd));
                $eventDay = date('Y-m-d', strtotime($meeting['meeting_time']));
                $startTime = date('H:i', strtotime($meeting['meeting_time']));
                $calendarText = date('H:i', strtotime($meeting['meeting_time'])) . ' ' . $meetingTypeTitle;
            }

            $preparationTime = !empty($meetingType['preparation_time']) ? $meetingType['preparation_time'] : 0;
            $postBufferTime = !empty($meetingType['post_buffer_time']) ? $meetingType['post_buffer_time'] : 0;
            
            $formattedmeeting = [
                'id' => $meeting['id'],
                'title' => $meetingTypeTitle,
                'event_day' => $eventDay,
                'start_time' => $startTime,
                'start_date_formatted' => $startDateFormatted,
                'start_time_formatted' => $startTimeFormatted,
                'calendar_text' => $calendarText,
                'event_start' => $meeting['meeting_time'],
                'event_end' => $eventEnd,
                'end_date_formatted' => $endDateFormatted,
                'end_time_formatted' => $endTimeFormatted,
                'color' => 'blue-2',
                'property' => $property ?? null,
                'description' =>  $meetingTypeDescription,
                'venue' => '',
                'event_type' => 'meeting',
                'preparation_time' => $preparationTime,
                'post_buffer_time' => $postBufferTime,
                'participants' => $meetingParticipants
            ];
            
            $validmeetings[] = $formattedmeeting;
        }
    } else {
        $validmeetings = [];
    }
    
    // Get regular events
    $regularEvents = [];
    $eventParticipants = $db->getAllByQuery(
    'SELECT DISTINCT e.*, ed.start_date, ed.end_date, ed.id as event_date_id
    FROM event e
    JOIN event_date ed ON e.id = ed.event_id
    LEFT JOIN event_participant ep ON e.id = ep.event_id
    AND ep.contact_id = :contact_id
    AND (ep.event_date_id = ed.id OR ep.event_date_id IS NULL)
    LEFT JOIN event_moderator em ON ed.id = em.event_date_id
    AND em.contact_id = :contact_id
    WHERE ep.contact_id IS NOT NULL OR em.contact_id IS NOT NULL',
    ['contact_id' => $user['contact_id']]
    );
    
    foreach ($eventParticipants as $regularEvent) {
        // Get all participants for this specific event date
        $eventParticipantsList = $db->getAllByQuery(
        'SELECT c.id, c.full_name, c.email, "confirmed" as status
        FROM event_participant ep
        JOIN contact c ON ep.contact_id = c.id
        WHERE ep.event_id = :event_id AND ep.event_date_id = :event_date_id',
        ['event_id' => $regularEvent['id'], 'event_date_id' => $regularEvent['event_date_id']]
        );
        
        $eventDescription = !empty($regularEvent['description']) ? translate($regularEvent['description']) : '';
        if (!empty($eventDescription) && !containsHtml($eventDescription)) {
            $eventDescription = formatText($eventDescription);
        }
        
        // Convert times to specified timezone if provided
        if ($timezone) {
            $utcStartDate = new \DateTime($regularEvent['start_date'], new \DateTimeZone('UTC'));
            $utcStartDate->setTimezone(new \DateTimeZone($timezone));
            $utcEndDate = new \DateTime($regularEvent['end_date'], new \DateTimeZone('UTC'));
            $utcEndDate->setTimezone(new \DateTimeZone($timezone));
            
            $eventDay = $utcStartDate->format('Y-m-d');
            $startTime = $utcStartDate->format('H:i');
            $startDateFormatted = $utcStartDate->format($phpDateFormat);
            $startTimeFormatted = $utcStartDate->format($phpTimeFormat);
            $endDateFormatted = $utcEndDate->format($phpDateFormat);
            $endTimeFormatted = $utcEndDate->format($phpTimeFormat);
            $calendarText = $utcStartDate->format('H:i') . ' ' . translate($regularEvent['title'] ?? '');
        } else {
            $eventDay = date('Y-m-d', strtotime($regularEvent['start_date']));
            $startTime = date('H:i', strtotime($regularEvent['start_date']));
            $startDateFormatted = date($phpDateFormat, strtotime($regularEvent['start_date']));
            $startTimeFormatted = date($phpTimeFormat, strtotime($regularEvent['start_date']));
            $endDateFormatted = date($phpDateFormat, strtotime($regularEvent['end_date']));
            $endTimeFormatted = date($phpTimeFormat, strtotime($regularEvent['end_date']));
            $calendarText = date('H:i', strtotime($regularEvent['start_date'])) . ' ' . translate($regularEvent['title'] ?? '');
        }
        
        $formattedEvent = [
            'id' => $regularEvent['id'],
            'event_date_id' => $regularEvent['event_date_id'],
            'title' => translate($regularEvent['title'] ?? ''),
            'event_day' => $eventDay,
            'start_time' => $startTime,
            'start_date_formatted' => $startDateFormatted,
            'start_time_formatted' => $startTimeFormatted,
            'calendar_text' => $calendarText,
            'event_start' => $regularEvent['start_date'],
            'event_end' => $regularEvent['end_date'],
            'preparation_time' => !empty($regularEvent['preparation_time']) ? $regularEvent['preparation_time'] : 0,
            'post_buffer_time' => !empty($regularEvent['post_buffer_time']) ? $regularEvent['post_buffer_time'] : 0,
            'end_date_formatted' => $endDateFormatted,
            'end_time_formatted' => $endTimeFormatted,
            'color' => 'green-2',
            'description' => $eventDescription,
            'venue' => '',
            'event_type' => 'event',
            'participants' => $eventParticipantsList ?: []
        ];
        
        $regularEvents[] = $formattedEvent;
    }
    
    // Combine all event types
    $result = array_merge($events, $validmeetings, $regularEvents);
    
    // Sort all events by date and time
    usort($result, function($a, $b) {
        $aDateTime = isset($a['meeting_time']) ? $a['meeting_time'] :
        (isset($a['inspection_date']) ? $a['inspection_date'] :
        (isset($a['event_start']) ? $a['event_start'] :
        ($a['start_date'] ?? date('Y-m-d H:i:s'))));
        
        $bDateTime = isset($b['meeting_time']) ? $b['meeting_time'] :
        (isset($b['inspection_date']) ? $b['inspection_date'] :
        (isset($b['event_start']) ? $b['event_start'] :
        ($b['start_date'] ?? date('Y-m-d H:i:s'))));
        
        return strtotime($aDateTime) - strtotime($bDateTime);
    });
    
    return $result;
}
?>