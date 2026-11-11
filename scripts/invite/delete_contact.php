<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');   
die('unused');
    $data['user_id'] = $user_id;
    $db->update('contact',['deleted'=>1],['id'=>$data['id'],'user_id'=>$user_id]);
    success();
?>