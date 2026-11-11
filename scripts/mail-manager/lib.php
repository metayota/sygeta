<?php namespace Metayota; include_once('../metayota/library.php');  

function makeAPICall($endpoint, $data) {
    // Add API key to data
    $data['api_key'] = getConfig('metayota_api_key');
    
    // The endpoint URL
    $url = 'https://static.metayota.com/call/mail-manager-api/' . $endpoint;
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Referer: https://sygeta.com'  // Add referrer for CSRF check
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Enable SSL verification
    
    // Execute the request
    $response = curl_exec($ch);
    
    // Get detailed info
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    // Close cURL
    curl_close($ch);
    
    // Check for cURL errors
    if ($error) {
        return false;
    }
    
    // Check HTTP status
    if ($httpCode >= 400) {
        return false;
    }
    
    return $response;
}

?>