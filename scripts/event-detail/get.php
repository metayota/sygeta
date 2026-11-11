<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');   
    include_once('../contacts-lib/contacts-lib.php');
    $event = $db->getById('event', $data['id']);
    $event['title_translated'] = translate($event['title']);
    $event['description_translated'] = translate($event['description']);
    
    $eventDates = $db->getAll('event_date', ['event_id' => $event['id']]);
    $dateFormat = translate('php_datetime_format');
    $timeFormat = translate('php_time_format');
    $timezone = getTimezoneForCurrentUser();
    
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
    
    $participants = $db->getByQuery(
        "SELECT count(wp.id) as count FROM `event_participant` wp LEFT JOIN contact c ON c.id = wp.contact_id WHERE wp.event_id=:event_id;",
        ['event_id' => $event['id']]
    );
    $event['participants'] = $participants;
    
    echo json_encode($event);
?>