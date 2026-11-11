<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-helper/mail-helper.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');   
    include_once('../contacts-lib/contacts-lib.php');
    
    $event = $db->getByName('event',$data['event_name']);
    
    $event['title_translated'] = translate($event['title']);
    $event['description_translated'] = translate($event['description']);
    $eventDates = $db->getAll('event_date',['event_id'=>$event['id']]);
    $dateFormat = translate('php_datetime_format');
    
    // Get user's timezone from $data
    $timezone = $data['timezone'] ?? 'UTC';
    
    foreach($eventDates as &$eventDate) {
        // Convert start_date from UTC to user's timezone
        $utcStartDate = new \DateTime($eventDate['start_date'], new \DateTimeZone('UTC'));
        $utcStartDate->setTimezone(new \DateTimeZone($timezone));
        $eventDate['start_date_formatted'] = $utcStartDate->format($dateFormat);
        
        // Convert end_date from UTC to user's timezone
        $utcEndDate = new \DateTime($eventDate['end_date'], new \DateTimeZone('UTC'));
        $utcEndDate->setTimezone(new \DateTimeZone($timezone));
        $eventDate['end_time_formatted'] = $utcEndDate->format('H:i');
    }
    
    $event['dates'] = $eventDates;
    echo json_encode($event);
?>