<?php namespace Metayota; include_once('../metayota/library.php');  
    $event_id = $data['event_id'];
    $participants = $db->getAllByQuery('SELECT c.full_name, c.email FROM event_participant wp JOIN contact c ON wp.contact_id = c.id WHERE wp.event_id = :event_id', ['event_id'=>$event_id]);
    echo json_encode($participants);
?>