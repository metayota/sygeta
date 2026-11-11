<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    include_once('../access-check/access-check.php');
    checkWritePermissions('contact', $data['contact_id']);
    $newActivity = [];
    $partner = $db->get('partner',['user_id'=>$user_id]);
    if ($partner) {
        $newActivity['partner_id'] = $partner['id'];
    }

    $activityType = $db->getByName('activity_type', $data['activity_type']);
    $newActivity['activity_type_id'] = $activityType['id'];
    $newActivity['contact_id'] = $data['contact_id'];
    $newActivity['title'] = $data['title'];
    $newActivity['activity_date'] = date('Y-m-d');
    $db->insert('contact_activity', $newActivity);
    success('thank_you_for_submitting_activity');
?>