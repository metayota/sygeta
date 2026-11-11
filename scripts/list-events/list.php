<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    include_once('../contacts-lib/contacts-lib.php');
    
    $events = ($db->getAll('event'));
    $dateFormat = translate('php_datetime_format');
    $timeFormat = translate('php_time_format');
    $timezone = getTimezoneForCurrentUser();
    
    foreach($events as &$event) {
        $event['title_translated'] = translate($event['title']);
        $event['description_translated'] = translate($event['description']);
        
        // SQL Query für zukünftige Termine des jeweiligen Events
        $query = "SELECT * FROM event_date WHERE event_id = :event_id AND end_date > NOW()";
        $eventDates = $db->getAllByQuery($query, ['event_id'=>$event['id']]);
        
        foreach($eventDates as &$eventDate) {
            // Convert start_date from UTC to user timezone
            $utcStartDate = new \DateTime($eventDate['start_date'], new \DateTimeZone('UTC'));
            $utcStartDate->setTimezone(new \DateTimeZone($timezone));
            $eventDate['start_date_formatted'] = $utcStartDate->format($dateFormat);
            
            // Convert end_date from UTC to user timezone
            $utcEndDate = new \DateTime($eventDate['end_date'], new \DateTimeZone('UTC'));
            $utcEndDate->setTimezone(new \DateTimeZone($timezone));
            $eventDate['end_time_formatted'] = $utcEndDate->format($timeFormat);
        }
        
        $event['dates'] = $eventDates;
    }
    
    // Events ohne zukünftige Termine entfernen
    $events = array_filter($events, function($event) {
        return !empty($event['dates']);
    });
    
    echo json_encode(array_values($events));
?>