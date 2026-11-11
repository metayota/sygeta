<?php namespace Metayota; include_once('../metayota/library.php');  
    include_once('../access-check/access-check.php');
    checkReadPermissions('partner', $data['id']);

    $id = $data['id'];
    $partner = $db->getById('partner', $id);
    if ($partner) {
        $user = $db->getById('user', $partner['user_id']);
        $partner['username'] = $user['username'];
        $partner['email'] = $user['email'];
    }
    echo json_encode($partner);
?>