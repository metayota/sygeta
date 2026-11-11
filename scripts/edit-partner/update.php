<?php namespace Metayota; include_once('../metayota/library.php');  
    include_once('../access-check/access-check.php');
    checkWritePermissions('partner', $data['id']);
    $id = $data['id'];
    $partner = $db->getById('partner', $id);
    
    if ($partner) {
        $db->update('partner', [
            'full_name' => $data['full_name']
        ], ['id' => $id]);
        
        $db->update('user', [
            'username' => $data['username'],
            'email' => $data['email']
        ], ['id' => $partner['user_id']]);
    }
    
    echo json_encode(['status' => 'success']);
?>