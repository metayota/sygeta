<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  

include('../calendar/lib.php');
include_once('../contacts-lib/contacts-lib.php');

function getAllEvents($db, $userID, $contactTimezone) {
    $calendarEvents = getEventsForUser($userID,'UTC');
    
    $allEvents = array();
    
    foreach ($calendarEvents as $event) {
        // Convert from UTC to contact's timezone immediately
        $start = new \DateTimeImmutable($event['event_start'], new \DateTimeZone('UTC'));
        $end = new \DateTimeImmutable($event['event_end'], new \DateTimeZone('UTC'));
        
        $start = $start->setTimezone(new \DateTimeZone($contactTimezone));
        $end = $end->setTimezone(new \DateTimeZone($contactTimezone));
        
        // Apply preparation time (subtract from start) and post-buffer time (add to end)
        if (!empty($event['preparation_time'])) {
            $start = $start->modify('-' . $event['preparation_time'] . ' minutes');
        }
        
        if (!empty($event['post_buffer_time'])) {
            $end = $end->modify('+' . $event['post_buffer_time'] . ' minutes');
        }
        
        $allEvents[] = array(
            'start' => $start,
            'end' => $end
        );
    }
    
    return $allEvents;
}

function getPartnerAvailability($db, $partner_id, $startDate, $endDate, $partnerTimezone, $contactTimezone) {
    $availability = $db->getAll('partner_availability', array('partner_id' => $partner_id));
    $weekdayMap = array(
        'MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6, 'SUN' => 7
    );
    
    $availabilityRanges = array();
    
    // Convert start and end dates to partner's timezone to iterate through days
    $current = new \DateTimeImmutable($startDate, new \DateTimeZone($contactTimezone));
    $current = $current->setTimezone(new \DateTimeZone($partnerTimezone));
    $end = new \DateTimeImmutable($endDate, new \DateTimeZone($contactTimezone));
    $end = $end->setTimezone(new \DateTimeZone($partnerTimezone));
    
    // Iterate through each day in the date range
    while ($current <= $end) {
        $dayNum = (int)$current->format('N'); // 1 = Monday, 7 = Sunday
        
        // Check if partner has availability for this day of week
        foreach ($availability as $slot) {
            if ($weekdayMap[$slot['weekday']] === $dayNum) {
                // Create full datetime in partner's timezone
                $fromTime = $slot['time_from']; // e.g., "09:00:00"
                $toTime = $slot['time_to'];     // e.g., "17:00:00"
                
                $slotStart = new \DateTimeImmutable(
                    $current->format('Y-m-d') . ' ' . $fromTime, 
                    new \DateTimeZone($partnerTimezone)
                );
                $slotEnd = new \DateTimeImmutable(
                    $current->format('Y-m-d') . ' ' . $toTime, 
                    new \DateTimeZone($partnerTimezone)
                );
                
                // Convert to contact's timezone
                $slotStart = $slotStart->setTimezone(new \DateTimeZone($contactTimezone));
                $slotEnd = $slotEnd->setTimezone(new \DateTimeZone($contactTimezone));
                
                $availabilityRanges[] = array(
                    'start' => $slotStart,
                    'end' => $slotEnd
                );
            }
        }
        
        $current = $current->modify('+1 day');
    }
    
    return $availabilityRanges;
}

function findAvailableSlots($db, $partner_id, $startDate, $endDate, $contactTimezone, $partnerTimezone, $slotDuration = '1 hour', $bookingInterval = '30 minutes') {
    $partner = $db->getById('partner', $partner_id);
    
    $allEvents = getAllEvents($db, $partner['user_id'], $contactTimezone);
    $partnerAvailability = getPartnerAvailability($db, $partner_id, $startDate, $endDate, $partnerTimezone, $contactTimezone);
    $availableSlots = array();
    
    // Work in contact's timezone from the start
    $current = new \DateTimeImmutable($startDate, new \DateTimeZone($contactTimezone));
    $end = new \DateTimeImmutable($endDate, new \DateTimeZone($contactTimezone));
    
    while ($current < $end) {
        $slotEnd = $current->modify('+' . $slotDuration);
        
        // Check if this slot is within any partner availability range
        $isWithinPartnerAvailability = false;
        foreach ($partnerAvailability as $availableRange) {
            if ($current >= $availableRange['start'] && $slotEnd <= $availableRange['end']) {
                $isWithinPartnerAvailability = true;
                break;
            }
        }
        
        if ($isWithinPartnerAvailability) {
            $isAvailable = true;
            foreach ($allEvents as $event) {
                if (($current >= $event['start'] && $current < $event['end']) ||
                    ($slotEnd > $event['start'] && $slotEnd <= $event['end']) ||
                    ($current <= $event['start'] && $slotEnd >= $event['end'])) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($isAvailable) {
                $availableSlots[] = array(
                    'start' => $current,
                    'end' => $slotEnd
                );
            }
        }
        
        $current = $current->modify('+' . $bookingInterval);
    }
    
    return $availableSlots;
}

// Example usage

if (!empty($data['access_key'])) {
    $contact = $db->get('contact', ['access_key'=>$data['access_key']]);
} else {
    $contact = $db->getById('contact', $_SESSION['loggedInUser']['contact_id']);
}

// Get the contact's timezone
$contactTimezone = getTimezoneForContact($contact['id']);

$partner = $db->getById('partner', $contact['partner_id']);
$partner_id = $partner['id'];

// Get partner's contact timezone
$partnerContact = $db->getById('contact', $partner['contact_id']);
$partnerTimezone = getTimezoneForContact($partnerContact['id']);

// Start and end dates in contact's timezone
$startDate = date('Y-m-d 00:00:00', strtotime('tomorrow'));
$endDate = date('Y-m-d 23:59:59', strtotime('+2 months', strtotime('tomorrow')));

$slotDuration = '1 hour';
$durationMinutes = 60;
$preparationTime = 0;
$postBufferTime = 0;
if (!empty($data['meeting_type'])) {
    $meetingType = $db->getByName('meeting_type', $data['meeting_type']);
    $slotDuration = $meetingType['duration'].' minutes';
    $durationMinutes = $meetingType['duration'];
}
if (!empty($data['meeting_invitation_id'])) {
    $meetingInvitation = $db->getById('meeting_invitation', $data['meeting_invitation_id']);
    $meetingData = json_decode($meetingInvitation['meeting_data'], true);
    if (!empty($meetingData['duration_minutes'])) {
        $slotDuration = $meetingData['duration_minutes'].' minutes';
        $durationMinutes = $meetingData['duration_minutes'];
    }
}

if (!empty($meetingType)) {
    if (!empty($meetingType['preparation_time'])) {
        $durationMinutes += $meetingType['preparation_time'];
        $preparationTime = $meetingType['preparation_time'];
    }
    if (!empty($meetingType['post_buffer_time'])) {
        $durationMinutes += $meetingType['post_buffer_time'];
        $postBufferTime = $meetingType['post_buffer_time'];
    }
    $slotDuration = $durationMinutes.' minutes';
}

$bookingInterval = '15 minutes';

$availableSlots = findAvailableSlots($db, $partner_id, $startDate, $endDate, $contactTimezone, $partnerTimezone, $slotDuration, $bookingInterval);

// Output of available time slots
$timeFormat = translate('php_time_format');
$dateFormat = translate('php_date_format');

$result = [];

foreach ($availableSlots as $slot) {
    if ($preparationTime > 0) {
        $slot['start'] = $slot['start']->modify('+'.$preparationTime.' minutes');
    }
    if ($preparationTime > 0) {
        $slot['end'] = $slot['end']->modify('+'.$postBufferTime.' minutes');
    }
    // Dates are already in contact's timezone, so grouping by day works correctly
    $slotDate = $slot['start']->format('Y-m-d');
    $slotTime = $slot['start']->format('H:i:s');
    
    $dateIndex = array_search($slotDate, array_column($result, 'value'));
    
    if ($dateIndex === false) {
        $weekdayEnglish = $slot['start']->format('l');
        $weekdayTranslated = translate($weekdayEnglish);
        
        $result[] = [
            'name' => $weekdayTranslated . ', ' . $slot['start']->format($dateFormat),
            'value' => $slotDate,
            'times' => []
        ];
        $dateIndex = count($result) - 1;
    }
    
    $result[$dateIndex]['times'][] = [
        'name' => $slot['start']->format($timeFormat),
        'value' => $slotTime
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);

?>