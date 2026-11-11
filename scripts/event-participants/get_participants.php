<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

    $participants = $db->getAllByQuery('SELECT wp.*,c.firstname,c.lastname FROM event_participant wp LEFT JOIN contact c ON wp.contact_id = c.id ORDER BY id DESC');
    echo json_encode($participants);
?>