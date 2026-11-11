<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');   
die('unused');
    $subject = getInviteSubject($data['version'],$user_id);
    $text = getInviteText($data['version'],$user_id);

    echo json_encode(['subject'=>$subject,'text'=>$text]);
?>