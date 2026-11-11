<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../gemini-api/gemini-api.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  
include('../email-client/lib.php');


try {
    // Validate input parameters
    $folder = isset($data['folder']) ? $data['folder'] : 'INBOX';
    $page = isset($data['page']) ? max(1, (int)$data['page']) : 1;
    $perPage = 20;
    
    // Get user's email account
    $account = $db->get('email_account', ['user_id' => $user_id, 'id' => $data['account_id']]);
    
    if (!$account) {
        throw new \Exception('No email account configured');
    }
    
    // IMAP connection setup
    $isGmail = (strpos($account['imap_host'], 'gmail.com') !== false ||
                strpos($account['imap_host'], 'googleapis.com') !== false);
    
    if ($isGmail) {
        $mailbox = sprintf('{imap.gmail.com:993/imap/ssl/novalidate-cert}%s', $folder);
        $opts = [
            'DISABLE_AUTHENTICATOR' => ['PLAIN', 'GSSAPI', 'NTLM'],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];
    } else {
        $port = !empty($account['imap_port']) ? $account['imap_port'] : ($account['use_ssl'] ? 993 : 143);
        $mailbox = sprintf('{%s:%d%s/novalidate-cert}%s',
            $account['imap_host'],
            $port,
            $account['use_ssl'] ? '/imap/ssl' : '/imap',
            $folder
        );
        $opts = ['DISABLE_AUTHENTICATOR' => 'GSSAPI'];
    }
    
    // Set UTF-8 charset for IMAP connection
    $opts['CHARSET'] = 'UTF-8';
    
    $imap = @imap_open($mailbox, $account['imap_username'], $account['imap_password'], 0, 1, $opts);
    if (!$imap) {
        throw new \Exception('Failed to connect to IMAP server: ' . imap_last_error());
    }
    
    // Get mailbox information
    $check = imap_check($imap);
    $totalMessages = $check->Nmsgs;
    
    if ($totalMessages === 0) {
        imap_close($imap);
        echo json_encode([
            'status' => 'success',
            'data' => [
                'emails' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total_pages' => 0,
                    'total_messages' => 0,
                    'per_page' => $perPage
                ],
                'folder' => $folder
            ]
        ]);
        exit;
    }
    
    // Calculate pagination
    $totalPages = ceil($totalMessages / $perPage);
    $start = max(1, $totalMessages - ($page * $perPage));
    $end = max(1, $totalMessages - (($page - 1) * $perPage));
    
    // Fetch messages for current page
    $emails = [];
    for ($msgNum = $end; $msgNum >= $start; $msgNum--) {
        $header = imap_headerinfo($imap, $msgNum);
        $overview = imap_fetch_overview($imap, $msgNum)[0];
        
        // Format addresses
        $formatted_reply_to = '';
        if (!empty($header->reply_to)) {
            $formatted_reply_to = array_map(function($address) {
                $personal = isset($address->personal) ? decodeEmailSubject($address->personal) . ' ' : '';
                return sprintf('%s <%s@%s>', $personal, $address->mailbox, $address->host);
            }, $header->reply_to);
            $formatted_reply_to = implode(', ', $formatted_reply_to);
        }
        
        $to_string = '';
        if (isset($header->to) && !empty($header->to)) {
            $formatted_to = array_map(function($address) {
                $personal = isset($address->personal) ? decodeEmailSubject($address->personal) . ' ' : '';
                return sprintf('%s <%s@%s>', $personal, $address->mailbox, $address->host);
            }, $header->to);
            $to_string = implode(', ', $formatted_to);
        }
        
        // Build email data structure
        $emailData = [
            'message_num' => $msgNum,
            'subject' => isset($header->subject) ? decodeEmailSubject($header->subject) : '(No Subject)',
            'from' => isset($header->from[0]->mailbox) && isset($header->from[0]->host) ?
                $header->from[0]->mailbox . '@' . $header->from[0]->host : '',
            'to' => isset($header->to) ? $header->to : [],
            'to_formatted' => $to_string,
            'reply_to' => $header->reply_to,
            'reply_to_formatted' => $formatted_reply_to,
            'date' => date('Y-m-d H:i:s', strtotime($header->date)),
            'is_read' => ($header->Unseen === 'U') ? false : true,
            'has_attachments' => false,
            'flags' => $overview->flagged ? 'important' : 'normal',
            'messageId' => isset($header->message_id) ? $header->message_id : 
                         (isset($header->Messageid) ? $header->Messageid : 
                          md5($account['id'] . $folder . $msgNum . $header->date))
        ];
        
        // Check for attachments
        $structure = imap_fetchstructure($imap, $msgNum);
        if (isset($structure->parts) && count($structure->parts)) {
            $emailData['has_attachments'] = true;
        }
        
        $emails[] = $emailData;
    }
    
    imap_close($imap);
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'emails' => $emails,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_messages' => $totalMessages,
                'per_page' => $perPage
            ],
            'folder' => $folder
        ]
    ]);
    
} catch (\Exception $e) {
    if (isset($imap) && is_resource($imap)) {
        imap_close($imap);
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}