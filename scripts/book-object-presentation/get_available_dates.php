<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  

function getAllEvents($db) {
    $inspections = $db->getAll('property_inspection', array());
    $calendarEvents = $db->getAll('calendar_event', array());
    
    $allEvents = array();
    
    foreach ($inspections as $inspection) {
        $start = new \DateTimeImmutable($inspection['inspection_date'], new \DateTimeZone('UTC'));
        $end = $start->modify('+' . ($inspection['duration'] ?: '1 hour'));
        
        $allEvents[] = array(
            'start' => $start,
            'end' => $end
        );
    }
    
    foreach ($calendarEvents as $event) {
        $start = new \DateTimeImmutable($event['event_day'] . ' ' . $event['start_time'], new \DateTimeZone('UTC'));
        $end = new \DateTimeImmutable($event['event_day'] . ' ' . $event['end_time'], new \DateTimeZone('UTC'));
        
        $allEvents[] = array(
            'start' => $start,
            'end' => $end
        );
    }
    
    return $allEvents;
}

function getPartnerAvailability($db, $partner_id) {
    $availability = $db->getAll('partner_availability', array('partner_id' => $partner_id));
    $weekdayMap = array(
        'MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6, 'SUN' => 7
    );
    
    $availabilityByDay = array();
    foreach ($availability as $slot) {
        $dayNum = $weekdayMap[$slot['weekday']];
        $availabilityByDay[$dayNum][] = array(
            'from' => new \DateTimeImmutable($slot['time_from'], new \DateTimeZone('UTC')),
            'to' => new \DateTimeImmutable($slot['time_to'], new \DateTimeZone('UTC'))
        );
    }
    
    return $availabilityByDay;
}

function findAvailableSlots($db, $partner_id, $startDate, $endDate, $slotDuration = '1 hour', $bookingInterval = '30 minutes') {
    $allEvents = getAllEvents($db);
    $partnerAvailability = getPartnerAvailability($db, $partner_id);
    $availableSlots = array();
    
    $current = new \DateTimeImmutable($startDate, new \DateTimeZone('UTC'));
    $end = new \DateTimeImmutable($endDate, new \DateTimeZone('UTC'));
    
    while ($current < $end) {
        $slotEnd = $current->modify('+' . $slotDuration);
        
        $dayOfWeek = $current->format('N');
        
        $isWithinPartnerAvailability = false;
        if (isset($partnerAvailability[$dayOfWeek])) {
            foreach ($partnerAvailability[$dayOfWeek] as $availableSlot) {
                $availableStart = $current->setTime(
                    (int)$availableSlot['from']->format('H'),
                    (int)$availableSlot['from']->format('i'),
                    (int)$availableSlot['from']->format('s')
                );
                $availableEnd = $current->setTime(
                    (int)$availableSlot['to']->format('H'),
                    (int)$availableSlot['to']->format('i'),
                    (int)$availableSlot['to']->format('s')
                );
                
                if ($current >= $availableStart && $slotEnd <= $availableEnd) {
                    $isWithinPartnerAvailability = true;
                    break;
                }
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
$partner_id = 1; // Assuming we're looking for partner with ID 1
$startDate = date('Y-m-d 00:00:00', strtotime('tomorrow'));
$endDate = date('Y-m-d 23:59:59', strtotime('+2 months', strtotime('tomorrow'))); // Changed to 1 week for faster processing
$slotDuration = '1 hour';
$bookingInterval = '30 minutes';

$availableSlots = findAvailableSlots($db, $partner_id, $startDate, $endDate, $slotDuration, $bookingInterval);

// Output of available time slots
$timeFormat = translate('php_time_only_format');
$dateFormat = translate('php_date_only_format');

$result = [];

foreach ($availableSlots as $slot) {
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