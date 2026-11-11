<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../gemini-api/gemini-api.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  
try {
    // Get email accounts for current user
    $accounts = $db->getAll('email_account', ['user_id' => $user_id,'id'=>$data['account_id']]);
    
    if (empty($accounts)) {
        echo json_encode(
            $accounts
        );
        die();
    }
    
    $all_folders = [];
    
    foreach ($accounts as $account) {
        // Validate required account fields
        if (empty($account['imap_host']) || empty($account['imap_username']) || empty($account['imap_password'])) {
            errorLog('Missing required IMAP settings for account: ' . $account['email']);
            continue;
        }
        
        // Build IMAP connection string with proper port validation
        // First detect if it's Gmail
        $isGmail = (strpos($account['imap_host'], 'gmail.com') !== false ||
        strpos($account['imap_host'], 'googleapis.com') !== false);
        
        // Set appropriate settings based on email provider
        if ($isGmail) {
            $port = 993; // Gmail always uses 993
            $mailbox = sprintf('{imap.gmail.com:993/imap/ssl/novalidate-cert}');
            
            // Gmail specific options
            $opts = [
                'DISABLE_AUTHENTICATOR' => ['PLAIN', 'GSSAPI', 'NTLM'],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ];
        } else {
            // Original settings for other providers
            $port = !empty($account['imap_port']) ? $account['imap_port'] : ($account['use_ssl'] ? 993 : 143);
            $mailbox = sprintf('{%s:%d%s/novalidate-cert}',
            $account['imap_host'],
            $port,
            $account['use_ssl'] ? '/imap/ssl' : '/imap'
            );
            
            $opts = [
                'DISABLE_AUTHENTICATOR' => 'GSSAPI'
            ];
        }
        
        // Try to connect with timeout
        $imap = @imap_open($mailbox, $account['imap_username'], $account['imap_password'], 0, 1, $opts);
        
        if (!$imap) {
            $errors = imap_errors();
            $lastError = imap_last_error();
            
            $errorMsg = ('IMAP connection details:');
            $errorMsg.=('Host: ' . $account['imap_host']);
            $errorMsg.=('Port: ' . $port);
            $errorMsg.=('SSL: ' . ($account['use_ssl'] ? 'Yes' : 'No'));
            $errorMsg.=('Errors: ' . print_r($errors, true));
            msg($errorMsg);
            continue;
        }
        
        try {
            // Get list of mailboxes/folders
            $folders = imap_list($imap, $mailbox, '*');

            // Custom sorting to put INBOX first
            usort($folders, function($a, $b) {
                if (stripos($a, 'INBOX') !== false) return -1;
                if (stripos($b, 'INBOX') !== false) return 1;
                return strcasecmp($a, $b);
            });

            
            if ($folders === false) {
                throw new \Exception('Failed to get folder list: ' . imap_last_error());
            }
            
            $account_folders = [];
            foreach ($folders as $folder) {
                // Clean up folder name
                $folder_name = str_replace($mailbox, '', imap_utf7_decode($folder));
                $folder_name = trim(str_replace(['{', '}'], '', $folder_name));
                
                try {
                    // Get message count for folder with timeout protection
                    $folder_info = @imap_status($imap, $folder, SA_MESSAGES);
                    $message_count = $folder_info ? $folder_info->messages : 0;
                    
                    $account_folders[] = [
                        'name' => $folder_name,
                        'full_path' => $folder,
                        'message_count' => $message_count
                    ];
                } catch (\Exception $e) {
                    errorLog('Error getting status for folder ' . $folder_name . ': ' . $e->getMessage());
                }
            }
            
            if (!empty($account_folders)) {
                $all_folders[] = [
                    'account_id' => $account['id'],
                    'email' => $account['email'],
                    'folders' => $account_folders
                ];
            }
            
        } catch (\Exception $e) {
            errorLog('Error processing account ' . $account['email'] . ': ' . $e->getMessage());
        } finally {
            // Close IMAP connection
            if (is_resource($imap)) {
                imap_close($imap);
            }
        }
    }
    
    if (empty($all_folders)) {
        throw new \Exception('No folders found in any account');
    }
    
    // Return success response with cache headers
    header('Content-Type: application/json');
    header('Cache-Control: private, max-age=60');
    echo json_encode([
        'status' => 'success',
        'data' => $all_folders
    ]);
    
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>