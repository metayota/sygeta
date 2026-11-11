<?php namespace Metayota; include_once('../metayota/library.php');   
    $contact = $db->getById('contact', $data['id']);
    echo json_encode($contact);
?>