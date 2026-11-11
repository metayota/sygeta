<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    // Try to find contact first
    $contact = $db->get('contact', ['access_key'=>$data['access_key']]);
    
    // Try to find user (either by contact or by user_id)
    if (!empty($contact)) {
        $user = $db->get('user', ['contact_id'=>$contact['id']]);
    } else {
        $user = $db->getById('user', $user_id);
        if (!empty($user['contact_id'])) {
            $contact = $db->getById('contact', $user['contact_id']);
        }
    }
    
    // Get new timezone
    $timezone = $db->getByName('timezone', $data['timezone']);
    
    // Check current timezone (prefer contact, fallback to user)
    $currentTimezone = !empty($contact['timezone']) ? $contact['timezone'] : (!empty($user['timezone']) ? $user['timezone'] : null);
    
    // Should we update? (no timezone set OR forced update)
    $shouldUpdate = (empty($currentTimezone) || !empty($data['update_timezone']));
    
    if ($shouldUpdate) {
        // Update both (they stay in sync)
        if ($contact) $db->update('contact', ['timezone'=>$timezone['name']], ['id'=>$contact['id']]);
        if ($user) $db->update('user', ['timezone'=>$timezone['name']], ['id'=>$user['id']]);
        
        // Success message
        if ($contact || $user) {
            $message = empty($currentTimezone) ? 'your_timezone_detected' : 'your_timezone_updated';
            echo json_encode(['success'=>true, 'message'=>translate($message, $timezone['name']), 'timezone_changed'=>true, 'timezone'=>$timezone['name']]);
        } else {
            echo json_encode([]);
        }
        
    } else if ($timezone['name'] != $currentTimezone) {
        // Ask if they want to change
        echo json_encode([
            'success'=>true,
            'update_btn'=>translate('change_timezone'),
            'message'=>translate('ask_change_timezone', ['old_timezone'=>$currentTimezone, 'new_timezone'=>$timezone['name']]),
            'ask_timezone_change'=>true
        ]);
    } else {
        echo json_encode([]);
    }
?>