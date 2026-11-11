<?php namespace Metayota; include_once('../metayota/library.php');  
    $userData = [
        'username' => $data['username'],
        'email' => $data['email'],
        'usergroup_id' => 2,
        'status' => 'active',
        'register_date' => date('Y-m-d H:i:s')
    ];
    
    $userId = $db->insert('user', $userData);
    
    $partnerData = [
        'user_id' => $userId,
        'full_name' => $data['full_name']
    ];
    
    $db->insert('partner', $partnerData);
    
    callAction('mail-engine', 'send', [
        'template' => 'partner_welcome',
        'to' => $data['email'],
        'data' => ['username' => $data['username']]
    ]);
    
    echo json_encode(['status' => 'success']);
?>