<?php namespace Metayota; include_once('../metayota/library.php');  
try {
    
    // Fetch all email accounts for the current user
    $email_accounts = $db->getAllByQuery(
        "SELECT id, email , from_name
         FROM email_account 
         WHERE user_id = :user_id 
         ORDER BY email ASC",
        ['user_id' => $user_id]
    );
    
    if ($email_accounts === false) {
        throw new Exception('Failed to fetch email accounts');
    }
    
    // Return success response with email accounts data
    echo json_encode([
        'status' => 'success',
        'data' => $email_accounts
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>