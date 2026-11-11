<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');   
    $ip = $_SERVER['REMOTE_ADDR'];
    $useragent = $_SERVER['HTTP_USER_AGENT'];
    $languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $invite_id = $data['invite_id'];
    if (!empty($invite_id)) {
        $existingClick = $db->get('invitation_click',['ip_address'=>$ip]);
        $clickData = ['ip_address'=>$ip,'useragent'=>$useragent,'languages'=>$languages,'invite_id'=>$invite_id];
        $user_id = stringToNumber($invite_id);
        $clickData['user_id'] = $user_id;
        if (empty($existingClick)) {
            $db->insert('invitation_click',$clickData);
            claimBonus('link_click', $user_id);

        } else {
            $db->delete('invitation_click',['ip_address'=>$ip]);
            $db->insert('invitation_click',$clickData);
        }
    }
    success();
?>