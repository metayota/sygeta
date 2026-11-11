<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');   
die('unused');
    if (empty($user_id)) {
        errorMsg('msg_login_required');
    }

    $links = [];
    for($i = 0 ; $i < 3 ; $i++) {
        $links[] = 'https://www.alterworld.de/join/'.numberToString($user_id);
    }
    echo json_encode($links);
?>