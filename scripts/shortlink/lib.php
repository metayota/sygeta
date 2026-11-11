<?php namespace Metayota; include_once('../metayota/library.php');  

function createShortLink($originalUrl) {
    global $db;
    
    $shortCode = generateUniqueShortCode();
    
    $data = [
        'short_code' => $shortCode,
        'original_url' => $originalUrl,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $db->insert('shortlink', $data);
    
    return $shortCode;
}

function generateUniqueShortCode() {
    global $db;
    
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $maxAttempts = 100;
    
    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $shortCode = '';
        
        for ($i = 0; $i < 8; $i++) {
            $shortCode .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        $existing = $db->get('shortlink', ['short_code' => $shortCode]);
        
        if (!$existing) {
            return $shortCode;
        }
    }
    
    throw new Exception('Konnte keinen eindeutigen Short-Code generieren');
}

function getOriginalUrl($shortCode) {
    global $db;
    
    $shortlink = $db->get('shortlink', ['short_code' => $shortCode]);
    
    return $shortlink ? $shortlink['original_url'] : null;
}

?>