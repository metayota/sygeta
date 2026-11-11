<?php namespace Metayota; include_once('../metayota/library.php');  
    include_once('../access-check/access-check.php');


if ($data['command'] === 'get_message') {
    $messageId = $data['id'];
    $message = $db->getById('mail_message', $messageId);
    checkReadPermissions('contact', $message['receiver_contact_id']);
    if (!$message) {
        http_response_code(404);
        echo json_encode(['error' => 'Message not found']);
        exit;
    }
    
    echo json_encode($message);
}
?>