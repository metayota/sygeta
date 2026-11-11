<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');   
    $contacts = $db->getAll('contact',['user_id'=>$user_id,'deleted'=>0]);
    echo json_encode($contacts);
?>