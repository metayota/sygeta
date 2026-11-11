<?php namespace Metayota; include_once('../metayota/library.php');  
    $timezone = $db->getColumnById('user', 'timezone', $user_id);
    echo json_encode(['timezone'=>$timezone]);
?>