<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../gemini-api/gemini-api.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  
try {
    // Validate required parameters
    if (!isset($data['account_id']) || !isset($data['message_num']) || !isset($data['folder'])) {
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
        // Identify or create Trash folder
        $trashFolderCandidates = ['Trash', 'Deleted Items', 'Deleted Messages', '[Gmail]/Trash'];
        $trashFolder = null;
        
        $folders = imap_list($imap, sprintf('{%s:%d%s}', 
            $account['imap_host'],
            $account['imap_port'],
            $account['use_ssl'] ? '/imap/ssl' : '/imap'
        ), '*');

        foreach ($trashFolderCandidates as $candidate) {
            foreach ($folders as $folder) {
                if (stripos($folder, $candidate) !== false) {
                    $trashFolder = $candidate;
                    break 2;
                }
            }
        }

        // If Trash folder doesn't exist, create it
        if (!$trashFolder) {
            $trashFolder = 'Trash';
            $newFolder = sprintf('{%s:%d%s}%s',
                $account['imap_host'],
                $account['imap_port'],
                $account['use_ssl'] ? '/imap/ssl' : '/imap',
                $trashFolder
            );

            if (!imap_createmailbox($imap, imap_utf7_encode($newFolder))) {
                throw new \Exception('Could not create Trash folder: ' . imap_last_error());
            }
        }

        // Move email to Trash folder
        if (!imap_mail_move($imap, $data['message_num'], $trashFolder)) {
            throw new \Exception(translate('failed_to_move_email') . ': ' . imap_last_error());
        }

        // Expunge original message
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