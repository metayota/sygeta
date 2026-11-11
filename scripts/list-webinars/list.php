<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $webinars = ($db->getAll('webinar'));
    $dateFormat = translate('php_date_format');
    
    foreach($webinars as &$webinar) {
        $webinar['title_translated'] = translate($webinar['title']);
        $webinar['description_translated'] = translate($webinar['description']);
        
        // SQL Query für zukünftige Termine des jeweiligen Webinars
        $query = "SELECT * FROM webinar_date WHERE webinar_id = :webinar_id AND end_date > NOW()";
        $webinarDates = $db->getAllByQuery($query, ['webinar_id'=>$webinar['id']]);
        
        foreach($webinarDates as &$webinarDate) {
            $webinarDate['start_date_formatted'] = date($dateFormat, strtotime($webinarDate['start_date']));
            $webinarDate['end_time_formatted'] = date('H:i', strtotime($webinarDate['end_date']));
        }
        
        $webinar['dates'] = $webinarDates;
    }
    
    // Webinare ohne zukünftige Termine entfernen
    $webinars = array_filter($webinars, function($webinar) {
        return !empty($webinar['dates']);
    });
    
    echo json_encode(array_values($webinars));
?>