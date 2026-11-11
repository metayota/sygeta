<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $user = $db->getById('user', $user_id);
    echo json_encode($db->getById('contact', $user['contact_id']));
?>