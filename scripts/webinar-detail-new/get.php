<?php namespace Metayota; include_once('../metayota/library.php');   
    $webinar = $db->getById('webinar',$data['id']);
    echo json_encode($webinar);

?>