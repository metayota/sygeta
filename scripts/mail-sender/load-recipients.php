<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
/**
 * load-recipients.php
 * Loads email recipients from database for mail sender component
 */

// Get search term if provided
$searchTerm = isset($data['search']) ? $data['search'] : '';

// Build query conditions
$conditions = [];
$params = [];

if (!empty($searchTerm)) {
    $searchTerm = '%' . $searchTerm . '%';
    $conditions[] = "(email LIKE :search OR name LIKE :search OR department LIKE :search)";
    $params['search'] = $searchTerm;
}

// Build complete query
$sql = "SELECT id, email, name, department FROM mail_recipients";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY name ASC";

try {
    // Execute query
    $recipients = $db->getAllByQuery($sql, $params);
    
    // Format recipients for dropdown
    $formattedRecipients = array_map(function($recipient) {
        return [
            'id' => $recipient['id'],
            'value' => $recipient['email'],
            'label' => $recipient['name'] ? 
                      "{$recipient['name']} <{$recipient['email']}>" : 
                      $recipient['email'],
            'department' => $recipient['department']
        ];
    }, $recipients);

    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $formattedRecipients
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load recipients: ' . $e->getMessage()
    ]);
}
?>