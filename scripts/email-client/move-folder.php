<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../gemini-api/gemini-api.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  
try {
    // Validate required parameters
    if (!isset($data['account_id']) || !isset($data['message_id']) || !isset($data['folder'])) {
        throw new \Exception(translate('missing_required_parameters'));
    }

    // Get account details and verify ownership
    $account = $db->getById('email_account', $data['account_id']);
    if (!$account || $account['user_id'] != $user_id) {
        throw new \Exception(translate('access_denied'));
    }

    // Connect to IMAP server
    $mailbox = sprintf('{%s:%d%s}%s',
        $account['imap_host'],
        $account['imap_port'],
        $account['use_ssl'] ? '/imap/ssl' : '/imap',
        $data['folder']
    );

    $imap = imap_open($mailbox, $account['imap_username'], $account['imap_password']);
    if (!$imap) {
        throw new \Exception(translate('imap_connection_failed') . ': ' . imap_last_error());
    }

    try {
 
        // Move email to Spam folder in IMAP
    $folders = imap_list($imap, sprintf('{%s:%d%s}', 
        $account['imap_host'],
        $account['imap_port'],
        $account['use_ssl'] ? '/imap/ssl' : '/imap'
    ), '*');
    
    $spamFolderName = $data['target_folder'];
    $hasSpamFolder = false;
    
    foreach($folders as $folder) {
        if(strpos($folder, $spamFolderName) !== false) {
            $hasSpamFolder = true;
            break;
        }
    }
    
    if(!$hasSpamFolder) {
        // Erstelle Spam-Ordner
        $newFolder = sprintf('{%s:%d%s}%s',
            $account['imap_host'],
            $account['imap_port'],
            $account['use_ssl'] ? '/imap/ssl' : '/imap',
            $spamFolderName
        );
        
        if(!imap_createmailbox($imap, $newFolder)) {
            throw new \Exception('Konnte Spam-Ordner nicht erstellen: ' . imap_last_error());
        }
    }

    // Jetzt können wir die Email verschieben
    if (!imap_mail_move($imap, $data['message_num'], $spamFolderName)) {
        throw new \Exception(translate('failed_to_move_email') . ': ' . imap_last_error());
    }


        // Expunge the original message
        imap_expunge($imap);

        // Close IMAP connection
        imap_close($imap);

        // Return success response
        success('moved_email_folder');

    } catch (\Exception $e) {
        if (is_resource($imap)) {
            imap_close($imap);
        }
        throw $e;
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>