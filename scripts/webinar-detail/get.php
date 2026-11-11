<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');   
    $event = $db->getById('event',$data['id']);
    $event['title_translated'] = translate($event['title']);
    $event['description_translated'] = translate($event['description']);
    $eventDates = $db->getAll('event_date',['event_id'=>$event['id']]);
    $dateFormat = translate('php_date_format');
    foreach($eventDates as &$eventDate) {
        $eventDate['start_date_formatted'] = date($dateFormat, strtotime($eventDate['start_date']));
        $eventDate['end_time_formatted'] = date('H:i', strtotime($eventDate['end_date']));
    }
    $event['dates'] = $eventDates;
    $participants = $db->getByQuery("SELECT count(wp.id) as count FROM `event_participant` wp LEFT JOIN contact c ON c.id = wp.contact_id WHERE wp.event_id=:event_id;",['event_id'=>$event['id']]);
    $event['participants'] = $participants;
    
    echo json_encode($event);

?>