<?php namespace Metayota; include_once('../metayota/library.php');   
    $eventType = $db->getById('event_type', $data['id']);
    echo json_encode($eventType);
?>