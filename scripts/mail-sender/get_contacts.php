<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $sql = "SELECT email as value, CONCAT(full_name, ' <', email, '>') as name FROM contact WHERE user_id=:user_id";
    $contactList = $db->getAllByQuery($sql,['user_id'=>$user_id]);
    echo json_encode($contactList);
?>