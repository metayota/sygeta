<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');   
die('unused');
    $user = $db->getById('user',$user_id);
    $shareLink = empty($data['share_link']) ? 'https://www.alterworld.de/' : $data['share_link'];
    $share_text = translate('share_text_'.rand(1,46), ['link'=>$shareLink,'username'=>$user['username']]);
    echo json_encode(['share_text'=>$share_text]);
?>