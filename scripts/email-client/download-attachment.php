<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../gemini-api/gemini-api.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  
// download-attachment.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(300); // 5 minutes

try {
    // Validate required parameters
    if (!isset($data['account_id']) || !isset($data['message_id']) || !isset($data['attachment_index'])) {
        throw new \Exception(translate('missing_required_parameters'));
    }

    // Sanitize input
    $account_id = filter_var($data['account_id'], FILTER_VALIDATE_INT);
    $message_num = filter_var($data['message_num'], FILTER_VALIDATE_INT);
    $attachment_index = trim($data['attachment_index']);

    if (!preg_match('/^[0-9.]+$/', $attachment_index)) {
        throw new \Exception('Invalid attachment index format');
    }

    if (!$account_id || !$message_num || !$attachment_index) {
        throw new \Exception('Invalid input parameters');
    }

    // Get account details and verify ownership
    $account = $db->getById('email_account', $account_id);
    if (!$account || $account['user_id'] != $user_id) {
        throw new \Exception(translate('access_denied'));
    }

    // Connect to IMAP server
    $mailbox = "{{$account['imap_host']}:{$account['imap_port']}/imap" . 
               ($account['use_ssl'] ? "/ssl/novalidate-cert" : "") . "}";
    $imap = imap_open($mailbox, $account['imap_username'], $account['imap_password']);
    
    if (!$imap) {
        throw new \Exception("IMAP Error: " . imap_last_error());
    }

    // Get message structure
    $structure = imap_fetchstructure($imap, $message_num);
    if (!$structure) {
        throw new \Exception("Failed to fetch message structure: " . imap_last_error());
    }

    // Find the requested attachment
    $attachments = [];
    if (isset($structure->parts)) {
        $flattenParts = function($messageParts, $prefix = '') use (&$flattenParts, &$attachments) {
            foreach ($messageParts as $partNumber => $part) {
                $currentPrefix = $prefix . ($prefix ? '.' : '') . ($partNumber + 1);
                
                $hasFilename = false;
                $filename = null;
                
                if (isset($part->dparameters)) {
                    foreach ($part->dparameters as $param) {
                        if (strtolower($param->attribute) == 'filename') {
                            $hasFilename = true;
                            $filename = $param->value;
                            break;
                        }
                    }
                }
                
                if (!$filename && isset($part->parameters)) {
                    foreach ($part->parameters as $param) {
                        if (strtolower($param->attribute) == 'name') {
                            $hasFilename = true;
                            $filename = $param->value;
                            break;
                        }
                    }
                }

                if ($hasFilename || 
                    (isset($part->disposition) && 
                     (strtolower($part->disposition) == 'attachment' || 
                      strtolower($part->disposition) == 'inline'))) {
                    
                    $attachments[] = [
                        'index' => $currentPrefix,
                        'filename' => $filename ?: "attachment-{$currentPrefix}",
                        'mime_type' => isset($part->subtype) ? 
                            strtolower($part->type) . '/' . strtolower($part->subtype) : 
                            'application/octet-stream',
                        'encoding' => $part->encoding,
                        'part' => $part
                    ];
                }
                
                if (isset($part->parts)) {
                    $flattenParts($part->parts, $currentPrefix);
                }
            }
        };
        
        $flattenParts($structure->parts);
    }

    // Get the specific attachment
    $attachment = null;
    foreach ($attachments as $att) {
        if ($att['index'] == $attachment_index) {
            $attachment = $att;
            break;
        }
    }

    if (!$attachment) {
        throw new \Exception(translate('attachment_not_found'));
    }

    // Log debug information
    error_log("Attachment mime type: " . $attachment['mime_type']);
    error_log("Attachment encoding: " . $attachment['encoding']);

    // Get attachment content
    $content = imap_fetchbody($imap, $message_num, $attachment['index']);
    if (!$content) {
        throw new \Exception("Failed to fetch attachment content: " . imap_last_error());
    }

    // Handle different encodings
    switch($attachment['encoding']) {
        case 3: // BASE64
            $content = base64_decode($content, true);
            if ($content === false) {
                throw new \Exception('Failed to decode BASE64 content');
            }
            break;
        case 4: // QUOTED-PRINTABLE
            $content = quoted_printable_decode($content);
            break;
        case 0: // 7BIT
        case 1: // 8BIT
        case 2: // BINARY
            // No decoding needed
            break;
        default:
            throw new \Exception('Unsupported message encoding: ' . $attachment['encoding']);
    }

    error_log("Content first 50 bytes: " . bin2hex(substr($content, 0, 50)));

    // Only apply charset conversion for text files
    if (strpos($attachment['mime_type'], 'text/') === 0 && isset($attachment['part']->parameters)) {
        foreach ($attachment['part']->parameters as $param) {
            if (strtolower($param->attribute) == 'charset') {
                $charset = $param->value;
                if (strtoupper($charset) != 'UTF-8') {
                    $content = iconv($charset, 'UTF-8//TRANSLIT', $content);
                }
                break;
            }
        }
    }

    // Sanitize filename
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $attachment['filename']);
    if (empty($filename)) {
        $filename = 'attachment-' . date('Y-m-d-H-i-s');
    }

    // Close IMAP connection before sending file
    imap_close($imap);

    // Make absolutely sure we're sending clean output
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Prevent any whitespace or other output
    error_reporting(0);

    // Disable compression
    if(ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }

    // Set headers for binary download
    header('Content-Type: ' . $attachment['mime_type']);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . strlen($content));
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: 0');

    // Output the raw binary data
    echo $content;
    exit;

} catch (\Exception $e) {
    if (isset($imap) && is_resource($imap)) {
        imap_close($imap);
    }
    
    error_log("Attachment download error: " . $e->getMessage());
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'debug_info' => [
            'imap_errors' => imap_errors(),
            'last_error' => imap_last_error()
        ]
    ]);
    exit;
}
?>