<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    include_once('../access-check/access-check.php');
    checkWritePermissions('contact', $data['contact_id']);
    $partner = $db->get('partner',['user_id'=>$user_id]);
    if ($partner) {
        $data['partner_id'] = $partner['id'];
    }

    $db->insert('contact_activity', $data);
    success('thank_you_for_submitting_activity');
?>